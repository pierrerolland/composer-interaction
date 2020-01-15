<?php

namespace RollAndRock;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use RollandRock\Exception\ExtraNotFoundException;

class ComposerInteractionPlugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;
    private array $configuration;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->configuration = $composer->getPackage()->getExtra()['rollandrock_interaction'] ?? [];

        if (empty($this->configuration) || !isset($this->configuration['questions'])) {
            throw new ExtraNotFoundException();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'post-root-package-install' => ['onPostRootInstall', 1]
        ];
    }

    public function onPostRootInstall(): void
    {
        $packagesToInstall = array_filter($this->configuration, fn($entry) => $entry['action'] === 'replace');

        var_dump($packagesToInstall);
        die;
    }
}
