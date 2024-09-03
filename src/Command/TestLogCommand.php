<?php

namespace Johndodev\JlogBundle\Command;

use Johndodev\JlogBundle\Console\LoggableOutputCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'jlog:test', description: 'Send some test logs')]
class TestLogCommand extends LoggableOutputCommand
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct();
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->logger->info('Info log');

        $io->success('Success output');

        $this->logger->error('Exception log', [
            'exception' => new \Exception('Exception message'),
        ]);

        return Command::SUCCESS;
    }
}
