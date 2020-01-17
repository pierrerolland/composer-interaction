<?php

namespace spec\RollAndRock;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\RootPackageInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RollAndRock\Asker;
use RollAndRock\ComposerInteractionPlugin;
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

class ComposerInteractionPluginSpec extends ObjectBehavior
{
    function let(
        Asker $asker,
        AddPackageHandler $addPackageHandler,
        EnvHandler $envHandler,
        Executor $executor,
        FileManager $fileManager,
        ReplaceHandler $replaceHandler
    ) {
        $this->beConstructedWith($asker, $addPackageHandler, $envHandler, $executor, $fileManager, $replaceHandler);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ComposerInteractionPlugin::class);
    }

    function its_activate_throws_exception_if_no_extra(
        Composer $composer,
        IOInterface $io,
        RootPackageInterface $package
    ): void
    {
        $composer->getPackage()->willReturn($package);
        $package->getExtra()->willReturn(null);

        $this->shouldThrow(ExtraNotFoundException::class)->during('activate', [$composer, $io]);
    }

    function its_activate_throws_exception_if_no_extra_with_bundle_integration(
        Composer $composer,
        IOInterface $io,
        RootPackageInterface $package
    ): void
    {
        $composer->getPackage()->willReturn($package);
        $package->getExtra()->willReturn([
            'symfony' => []
        ]);

        $this->shouldThrow(ExtraNotFoundException::class)->during('activate', [$composer, $io]);
    }

    function its_activate_throws_exception_if_no_questions_in_configuration(
        Composer $composer,
        IOInterface $io,
        RootPackageInterface $package
    ): void
    {
        $composer->getPackage()->willReturn($package);
        $package->getExtra()->willReturn([
            'rollandrock-interaction' => []
        ]);

        $this->shouldThrow(ExtraNotFoundException::class)->during('activate', [$composer, $io]);
    }

    function its_activate_works_with_questions_in_configuration(
        Composer $composer,
        IOInterface $io,
        RootPackageInterface $package
    ): void
    {
        $composer->getPackage()->willReturn($package);
        $package->getExtra()->willReturn([
            'rollandrock-interaction' => [
                'questions' => []
            ]
        ]);

        $this->shouldNotThrow(ExtraNotFoundException::class)->during('activate', [$composer, $io]);
    }

    function its_install_packages_should_throw_exception_if_question_conf_has_no_packages(
        Asker $asker,
        EnvHandler $envHandler,
        AddPackageHandler $addPackageHandler,
        Composer $composer,
        IOInterface $io,
        RootPackageInterface $package
    ): void
    {
        $composer->getPackage()->willReturn($package);
        $package->getExtra()->willReturn([
            'rollandrock-interaction' => [
                'questions' => [
                    [
                        'action' => 'add-package'
                    ]
                ]
            ]
        ]);

        $asker->askQuestion(Argument::any())->shouldNotBeCalled();
        $envHandler->addEnv(Argument::any())->shouldNotBeCalled();
        $addPackageHandler->addPackages(Argument::any())->shouldNotBeCalled();

        $this->activate($composer, $io);
        $this->shouldThrow(AddPackageQuestionHasNoPackagesException::class)->during('installPackages');
    }

    function its_install_packages_should_filter_questions_and_act_only_if_answer_is_positive(
        Asker $asker,
        EnvHandler $envHandler,
        AddPackageHandler $addPackageHandler,
        Composer $composer,
        IOInterface $io,
        RootPackageInterface $package
    ): void
    {
        $composer->getPackage()->willReturn($package);
        $package->getExtra()->willReturn([
            'rollandrock-interaction' => [
                'questions' => [
                    [
                        'action' => 'add-package',
                        'question' => 'question1',
                        'packages' => ['package1' => 'v1', 'package2' => 'v2'],
                        'env' => ['env1' => 'val1']
                    ],
                    [
                        'action' => 'add-package',
                        'question' => 'question2',
                        'packages' => ['package3' => 'v3', 'package4' => 'v4'],
                        'env' => ['env2' => 'val2']
                    ],
                    [
                        'action' => 'add-package',
                        'question' => 'question3',
                        'packages' => ['package5' => 'v5', 'package6' => 'v6']
                    ],
                    [
                        'action' => 'replace',
                        'question' => 'question4'
                    ]
                ]
            ]
        ]);

        $this->activate($composer, $io);
        $asker->askQuestion([
            'action' => 'add-package',
            'question' => 'question1',
            'packages' => ['package1' => 'v1', 'package2' => 'v2'],
            'env' => ['env1' => 'val1'],
            'type' => 'bool'
        ])->willReturn(false)->shouldBeCalledOnce();
        $asker->askQuestion([
            'action' => 'add-package',
            'question' => 'question2',
            'packages' => ['package3' => 'v3', 'package4' => 'v4'],
            'env' => ['env2' => 'val2'],
            'type' => 'bool'
        ])->willReturn(true)->shouldBeCalledOnce();
        $asker->askQuestion([
            'action' => 'add-package',
            'question' => 'question3',
            'packages' => ['package5' => 'v5', 'package6' => 'v6'],
            'type' => 'bool'
        ])->willReturn(true)->shouldBeCalledOnce();
        $asker->askQuestion([
            'action' => 'replace',
            'question' => 'question3',
            'type' => 'bool'
        ])->shouldNotBeCalled();

        $envHandler->addEnv(['env1' => 'val1'])->shouldNotBeCalled();
        $envHandler->addEnv(['env2' => 'val2'])->shouldBeCalledOnce();
        $envHandler->addEnv([])->shouldBeCalledOnce();

        $addPackageHandler->addPackages(['package1' => 'v1', 'package2' => 'v2'])->shouldNotBeCalled();
        $addPackageHandler->addPackages(['package3' => 'v3', 'package4' => 'v4'])->shouldBeCalledOnce();
        $addPackageHandler->addPackages(['package5' => 'v5', 'package6' => 'v6'])->shouldBeCalledOnce();

        $this->installPackages();
    }

    function its_replace_should_throw_an_exception_if_question_has_no_placeholders(
        Asker $asker,
        ReplaceHandler $replaceHandler,
        Composer $composer,
        IOInterface $io,
        RootPackageInterface $package
    ): void
    {
        $composer->getPackage()->willReturn($package);
        $package->getExtra()->willReturn([
            'rollandrock-interaction' => [
                'questions' => [
                    [
                        'action' => 'replace',
                        'question' => 'question1'
                    ]
                ]
            ]
        ]);

        $asker->askQuestion(Argument::any())->shouldNotBeCalled();
        $replaceHandler->replace(Argument::any())->shouldNotBeCalled();

        $this->activate($composer, $io);
        $this->shouldThrow(ReplaceQuestionHasNoPlaceholdersException::class)->during('replace');
    }

    function its_replace_should_throw_an_exception_if_question_has_a_bool_type(
        Asker $asker,
        ReplaceHandler $replaceHandler,
        Composer $composer,
        IOInterface $io,
        RootPackageInterface $package
    ): void
    {
        $composer->getPackage()->willReturn($package);
        $package->getExtra()->willReturn([
            'rollandrock-interaction' => [
                'questions' => [
                    [
                        'action' => 'replace',
                        'question' => 'question1',
                        'placeholders' => [
                            ['file' => 'test1.txt', 'placeholder' => '{1}']
                        ],
                        'type' => 'bool'
                    ]
                ]
            ]
        ]);

        $asker->askQuestion(Argument::any())->shouldNotBeCalled();
        $replaceHandler->replace(Argument::any())->shouldNotBeCalled();

        $this->activate($composer, $io);
        $this->shouldThrow(TypeBoolForbiddenException::class)->during('replace');
    }

    function its_replace_should_filter_questions_then_ask_then_call_replace_handler(
        Asker $asker,
        ReplaceHandler $replaceHandler,
        Composer $composer,
        IOInterface $io,
        RootPackageInterface $package
    ): void
    {
        $composer->getPackage()->willReturn($package);
        $package->getExtra()->willReturn([
            'rollandrock-interaction' => [
                'questions' => [
                    [
                        'action' => 'replace',
                        'question' => 'question1',
                        'placeholders' => [
                            ['file' => 'test1.txt', 'placeholder' => '{1}'],
                            []
                        ],
                    ],
                    [
                        'action' => 'add-package',
                        'question' => 'question2'
                    ]
                ]
            ]
        ]);

        $asker->askQuestion([
            'action' => 'replace',
            'question' => 'question1',
            'placeholders' => [
                ['file' => 'test1.txt', 'placeholder' => '{1}'],
                []
            ],
        ])->willReturn('answer1')->shouldBeCalledOnce();
        $asker->askQuestion([
            'action' => 'add-package',
            'question' => 'question2'
        ])->shouldNotBeCalled();
        $replaceHandler->replace('test1.txt', '{1}', 'answer1')->shouldBeCalledOnce();
        $replaceHandler->replace('nofile.txt', 'placeholder', 'answer1')->shouldBeCalledOnce();

        $this->activate($composer, $io);
        $this->replace();
    }

    function its_destroy_should_at_least_do_composer_remove_even_if_no_composer_json(
        Executor $executor,
        FileManager $fileManager,
        Composer $composer,
        IOInterface $io,
        RootPackageInterface $package
    ): void {
        $composer->getPackage()->willReturn($package);
        $package->getExtra()->willReturn([
            'rollandrock-interaction' => [
                'questions' => []
            ]
        ]);

        $fileManager->read('composer.json')->willThrow(FileNotFoundException::class)->shouldBeCalledOnce();
        $executor
            ->execute(sprintf('%s remove rollandrock/composer-interaction', $_SERVER['argv'][0]))
            ->shouldBeCalledOnce();
        $fileManager->write('composer.json', Argument::any())->shouldNotBeCalled();

        $this->activate($composer, $io);
        $this->destroy();
    }

    function its_destroy_should_do_composer_remove_and_rewrite_composer_json(
        Executor $executor,
        FileManager $fileManager,
        Composer $composer,
        IOInterface $io,
        RootPackageInterface $package
    ): void {
        $composer->getPackage()->willReturn($package);
        $package->getExtra()->willReturn([
            'rollandrock-interaction' => [
                'questions' => []
            ]
        ]);

        $fileManager
            ->read('composer.json')
            ->willReturn(file_get_contents(__DIR__ . '/../resources/composer.orig.json'))
            ->shouldBeCalledOnce();
        $executor
            ->execute(sprintf('%s remove rollandrock/composer-interaction', $_SERVER['argv'][0]))
            ->shouldBeCalledOnce();
        $fileManager
            ->write('composer.json', file_get_contents(__DIR__ . '/../resources/composer.expected.json'))
            ->shouldBeCalledOnce();

        $this->activate($composer, $io);
        $this->destroy();
    }
}
