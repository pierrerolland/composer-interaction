<?php

namespace spec\RollAndRock\Handler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RollAndRock\Handler\EnvHandler;
use RollAndRock\Util\Env;
use RollAndRock\Util\FileManager;

class EnvHandlerSpec extends ObjectBehavior
{
    function let(Env $env, FileManager $fileManager): void
    {
        $this->beConstructedWith($env, $fileManager);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(EnvHandler::class);
    }

    function its_add_env_should_do_nothing_with_empty_env(Env $env, FileManager $fileManager): void
    {
        $env->read()->shouldNotBeCalled();
        $fileManager->write('.env', Argument::type('string'), true)->shouldNotBeCalled();

        $this->addEnv([]);
    }

    function its_add_env_should_build_an_env_file_with_given_properties(Env $env, FileManager $fileManager): void
    {
        $env->read()->willReturn([
            'a' => 'first',
            'b' => 'second'
        ])->shouldBeCalledOnce();

        $expected = <<<ENV
a=first
b=second
property1=value1
property2=value2
ENV;


        $fileManager->write('.env', $expected, true)->shouldBeCalledOnce();

        $this->addEnv([
            'property1' => 'value1',
            'property2' => 'value2'
        ]);
    }
}
