<?php

declare(strict_types=1);

namespace Johndodev\JlogBundle\Console;

use Johndodev\JlogBundle\Console\Output\LoggableOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command à extends pour avoir automatiquement un output loggable dans execute()
 */
abstract class LoggableOutputCommand extends Command
{
    private LoggableOutput $loggableOutput;

    public function setLoggableOutput(LoggableOutput $loggableOutput): void
    {
        $this->loggableOutput = $loggableOutput;
    }

    public function getLoggableOutput(): LoggableOutput
    {
        return $this->loggableOutput;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->setCode(function (InputInterface $input, OutputInterface $output) {
            $this->setLoggableOutput(new LoggableOutput($output));

            return $this->execute($input, $this->getLoggableOutput());
        });
    }

    /**
     * @return string|null
     */
    public function getTerminateLogMessage(InputInterface $input, OutputInterface $output): ?string
    {
        return null;
    }
}
