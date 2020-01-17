<?php

namespace spec\RollAndRock\Handler;

use PhpSpec\ObjectBehavior;
use RollAndRock\Handler\ReplaceHandler;
use RollAndRock\Util\FileManager;

class ReplaceHandlerSpec extends ObjectBehavior
{
    function let(FileManager $fileManager): void
    {
        $this->beConstructedWith($fileManager);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ReplaceHandler::class);
    }

    function its_replace_replaces_content_with_placeholder(FileManager $fileManager): void
    {
        $fileManager->read('file')->willReturn('Hurray {ph} niang !')->shouldBeCalledOnce();
        $fileManager->write('file', 'Hurray mbaye niang !')->shouldBeCalledOnce();

        $this->replace('file', '{ph}', 'mbaye');
    }
}
