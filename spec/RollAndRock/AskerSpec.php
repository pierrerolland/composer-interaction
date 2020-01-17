<?php

namespace spec\RollAndRock;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RollAndRock\Asker;
use RollAndRock\Exception\ChoiceQuestionHasNoChoicesException;
use RollAndRock\Exception\UnknownQuestionTypeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class AskerSpec extends ObjectBehavior
{
    function let(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output): void
    {
        $this->beConstructedWith($questionHelper, $input, $output);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(Asker::class);
    }

    function its_ask_question_should_throw_an_exception_if_type_choice_and_no_choices(QuestionHelper $questionHelper)
    {
        $questionHelper->ask(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(ChoiceQuestionHasNoChoicesException::class)->during('askQuestion', [['type' => 'choice']]);
    }

    function its_ask_question_should_throw_an_exception_if_type_choice_and_empty_choices(QuestionHelper $questionHelper)
    {
        $questionHelper->ask(Argument::cetera())->shouldNotBeCalled();

        $this
            ->shouldThrow(ChoiceQuestionHasNoChoicesException::class)
            ->during('askQuestion', [['type' => 'choice', 'choices' => []]]);
    }

    function its_ask_question_should_return_string_if_free_question(
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output
    ) {
        $questionHelper
            ->ask($input, $output, Argument::type(Question::class))
            ->willReturn('answer')
            ->shouldBeCalledOnce();

        $this->askQuestion([
            'type' => 'free',
            'question' => 'question'
        ])->shouldReturn('answer');
    }

    function its_ask_question_should_return_string_if_choice_question(
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output
    ) {
        $questionHelper
            ->ask($input, $output, Argument::type(ChoiceQuestion::class))
            ->willReturn('answer1')
            ->shouldBeCalledOnce();

        $this->askQuestion([
            'type' => 'choice',
            'question' => 'question',
            'choices' => ['answer1', 'answer2']
        ])->shouldReturn('answer1');
    }

    function its_ask_question_should_return_true_if_bool_question_with_yes_answer(
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output
    ) {
        $questionHelper
            ->ask($input, $output, Argument::type(ChoiceQuestion::class))
            ->willReturn('yes')
            ->shouldBeCalledOnce();

        $this->askQuestion([
            'type' => 'bool',
            'question' => 'question'
        ])->shouldReturn(true);
    }

    function its_ask_question_should_return_false_if_bool_question_with_no_answer(
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output
    ) {
        $questionHelper
            ->ask($input, $output, Argument::type(ChoiceQuestion::class))
            ->willReturn('no')
            ->shouldBeCalledOnce();

        $this->askQuestion([
            'type' => 'bool',
            'question' => 'question'
        ])->shouldReturn(false);
    }

    function its_ask_question_should_throw_exception_if_unknown_type(QuestionHelper $questionHelper)
    {
        $questionHelper->ask(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(UnknownQuestionTypeException::class)->during('askQuestion', [[
            'type' => 'unknown',
            'question' => 'question'
        ]]);
    }
}
