<?php

declare(strict_types=1);

namespace Johndodev\JlogBundle\Console\Output;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Output qui permet de logger la sortie dans un buffer (en plus de l'output initial) et de le récupérer dans un listener
 * (dans le but de l'envoyer à jlog)
 */
class LoggableOutput extends StreamOutput
{
    private ?OutputInterface $decorated;

    public function __construct(OutputInterface $decorated)
    {
        parent::__construct(fopen('php://memory', 'w'), $decorated->getVerbosity());
        $this->decorated = $decorated;
    }

    public function write(iterable|string $messages, bool $newline = false, int $options = 0): void
    {
        parent::write($messages, $newline, $options);
        $this->decorated->write($messages, $newline, $options);
    }
}
