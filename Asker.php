<?php

namespace RollAndRock;

use RollAndRock\Exception\ChoiceQuestionHasNoChoicesException;
use RollAndRock\Exception\UnknownQuestionTypeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Asker
{
    private QuestionHelper $questionHelper;
    private InputInterface $input;
    private OutputInterface $output;

    public function __construct(
        QuestionHelper $questionHelper = null,
        InputInterface $input = null,
        OutputInterface $output = null
    ) {
        $this->questionHelper = is_null($questionHelper) ? new QuestionHelper() : $questionHelper;
        $this->input = is_null($input) ? new ArgvInput() : $input;
        $this->output = is_null($output) ? new ConsoleOutput() : $output;
    }

    public function askQuestion(array $questionConfig)
    {
        $questionConfig['type'] ??= 'free';

        if (
            $questionConfig['type'] === 'choice' &&
            (!isset($questionConfig['choices']) || empty($questionConfig['choices']))
        ) {
            throw new ChoiceQuestionHasNoChoicesException();
        }

        switch ($questionConfig['type']) {
            case 'free':
                $question = new Question($questionConfig['question']);
                break;
            case 'choice':
                $question = new ChoiceQuestion($questionConfig['question'], $questionConfig['choices']);
                break;
            case 'bool':
                $question = new ChoiceQuestion($questionConfig['question'], ['yes', 'no']);
                break;
            default:
                throw new UnknownQuestionTypeException();
        }

        $result = $this->questionHelper->ask($this->input, $this->output, $question);

        if ($questionConfig['type'] === 'bool') {
            return $result === 'yes';
        }

        return $result;
    }
}
