<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Linio\Component\Input\InputHandler;
use Linio\Component\Input\Constraint\Constraint;

class DateLessThan extends Constraint
{
    protected $date;

    public function __construct(\DateTime $date, $message = '')
    {
        $this->date = $date;

        $this->setErrorMessage('Invalid value, should be less than: ' . $date->format('Y-m-d'));
    }

    public function validate($content): bool
    {
        $value = new \DateTime($content);

        if ($value > $this->date) {
            return false;
        }

        return true;
    }
}

class TestHandler extends InputHandler
{
    public function define()
    {
        $yesterday = (new \DateTime())->add(\DateInterval::createFromDateString('yesterday'));

        $this->add(
            'date',
            'datetime',
            [
                'constraints' => [
                    new DateLessThan(
                        $yesterday
                    )
                ]
            ]
        );
    }
}

class TestCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:test-command';

    protected function configure()
    {
        $this
            ->addArgument('date', InputArgument::REQUIRED, 'date')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = [
            'date' => $input->getArgument('date')
        ];

        $handler = new TestHandler();
        $handler->bind($data);

        if (!$handler->isValid()) {
            throw new \Exception($handler->getErrorsAsString());
        }

        $date = $handler->getData("date");

        var_dump($date);
    }
}