<?php

namespace RollAndRock;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use RollAndRock\Exception\AddPackageQuestionHasNoPackagesException;
use RollAndRock\Exception\ExtraNotFoundException;
use RollAndRock\Exception\FileNotFoundException;
use RollAndRock\Exception\ReplaceQuestionHasNoPlaceholdersException;
use RollAndRock\Exception\TypeBoolForbiddenException;
use RollAndRock\Handler\AddPackageHandler;
use RollAndRock\Handler\EnvHandler;
use RollAndRock\Handler\ReplaceHandler;
use RollAndRock\Util\Executor;
use RollAndRock\Util\FileManager;

class ComposerInteractionPlugin implements PluginInterface, EventSubscriberInterface
{
    private AddPackageHandler $addPackageHandler;
    private Asker $asker;
    private Composer $composer;
    private EnvHandler $envHandler;
    private Executor $executor;
    private FileManager $fileManager;
    private IOInterface $io;
    private ReplaceHandler $replaceHandler;
    private array $configuration;

    public function __construct(
        Asker $asker = null,
        AddPackageHandler $addPackageHandler = null,
        EnvHandler $envHandler = null,
        Executor $executor = null,
        FileManager $fileManager = null,
        ReplaceHandler $replaceHandler = null
    ) {
        $this->asker = is_null($asker) ? new Asker() : $asker;
        $this->addPackageHandler = is_null($addPackageHandler) ? new AddPackageHandler() : $addPackageHandler;
        $this->envHandler = is_null($envHandler) ? new EnvHandler() : $envHandler;
        $this->executor = is_null($executor) ? new Executor() : $executor;
        $this->fileManager = is_null($fileManager) ? new FileManager() : $fileManager;
        $this->replaceHandler = is_null($replaceHandler) ? new ReplaceHandler() : $replaceHandler;
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->configuration = $composer->getPackage()->getExtra()['rollandrock-interaction'] ?? [];

        if (empty($this->configuration) || !isset($this->configuration['questions'])) {
            throw new ExtraNotFoundException();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'post-create-project-cmd' => [
                ['installPackages', 2],
                ['replace', 1],
                ['destroy', -1]
            ]
        ];
    }

    public function installPackages(): void
    {
        $questionsForPackageInstall = array_filter(
            $this->configuration['questions'],
            fn($entry) => $entry['action'] === 'add-package'
        );

        foreach ($questionsForPackageInstall as $question) {
            $question['type'] = 'bool';

            if (!isset($question['packages'])) {
                throw new AddPackageQuestionHasNoPackagesException();
            }

            if ($this->asker->askQuestion($question)) {
                $this->envHandler->addEnv($question['env'] ?? []);
                $this->addPackageHandler->addPackages($question['packages']);
            }
        }
    }

    public function replace(): void
    {
        $questionsForReplace = array_filter(
            $this->configuration['questions'],
            fn($entry) => $entry['action'] === 'replace'
        );

        foreach ($questionsForReplace as $question) {
            if (!isset($question['placeholders'])) {
                throw new ReplaceQuestionHasNoPlaceholdersException();
            }
            if (isset($question['type']) && $question['type'] === 'bool') {
                throw new TypeBoolForbiddenException();
            }

            $result = $this->asker->askQuestion($question);
            foreach ($question['placeholders'] as $placeholderConfig) {
                $this->replaceHandler->replace(
                    $placeholderConfig['file'] ?? 'nofile.txt',
                    $placeholderConfig['placeholder'] ?? 'placeholder',
                    $result
                );
            }
        }
    }

    public function destroy(): void
    {
        $this->executor->execute(sprintf('%s remove rollandrock/composer-interaction', $_SERVER['argv'][0]));

        try {
            $json = json_decode($this->fileManager->read('composer.json'));

            if (JSON_ERROR_NONE !== json_last_error()) {
                return;
            }

            if (property_exists($json, 'extra') && property_exists($json->extra, 'rollandrock-interaction')) {
                unset($json->extra->{'rollandrock-interaction'});
            }

            $this
                ->fileManager
                ->write('composer.json', json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) . "\n");
        } catch (FileNotFoundException $e) {
            // continue silently
        }
    }
}
