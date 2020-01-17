<?php

namespace spec\RollAndRock\Handler;

use PhpSpec\ObjectBehavior;
use RollAndRock\Handler\AddPackageHandler;
use RollAndRock\Util\Executor;

class AddPackageHandlerSpec extends ObjectBehavior
{
    function let(Executor $executor): void
    {
        $this->beConstructedWith($executor);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(AddPackageHandler::class);
    }

    function its_add_packages_should_execute_composer_require_on_all_packages(Executor $executor): void
    {
        $cmd = sprintf('%s require vendor/package1:^1.0 vendor/package2', $_SERVER['argv'][0]);
        $executor->execute($cmd)->shouldBeCalledOnce();

        $this->addPackages([
            'vendor/package1' => '^1.0',
            'vendor/package2' => '*'
        ]);
    }
}
