<?php

declare(strict_types=1);

namespace Johndodev\JlogBundle\Console;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Log les events des commandes SF (les commandes lancées, les erreurs, ...)
 * Le but étant d'avoir une visu dans jlog
 * La commande doit extends LoggableOutputCommand pour être loggée (sauf les erreurs pour tous)
 */
#[AsEventListener(method: 'onCommandStarted')]
#[AsEventListener(method: 'onCommandError')]
#[AsEventListener(method: 'onCommandFinished')]
class ConsoleEventListener
{
    private LoggerInterface $logger;
    private Stopwatch $stopwatch;

    public function __construct(LoggerInterface $logger, Stopwatch $stopwatch)
    {
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;
    }

    public function onCommandStart(ConsoleCommandEvent $event): void
    {
        if (!$event->getCommand() instanceof LoggableOutputCommand) {
            return;
        }

        $this->stopwatch->start(spl_object_hash($event->getCommand()));
    }

    /**
     * Log les exceptions dans les commandes dans le channel "command"
     * Même si la commande n'implemente pas "loggable"
     */
    public function onCommandError(ConsoleErrorEvent $event): void
    {
        $this->logger->error($event->getError()->getMessage(), array_merge(
            $this->getContext($event),
            ['exception' => $event->getError()],
        ));
    }

    public function onCommandTerminate(ConsoleTerminateEvent $event): void
    {
        // Si erreur, déjà loggé par onCommandError
        if ($event->getExitCode() !== Command::SUCCESS) {
            return;
        }

        if (!$event->getCommand() instanceof LoggableOutputCommand) {
            return;
        }

        $this->logger->info('Command executed: ' . $event->getCommand()->getName(), $this->getContext($event));
    }

    /**
     * @return array{command: string, options: mixed[], arguments: mixed[]}
     */
    private function getContext(ConsoleEvent $event): array
    {
        $context = [
            'command' => (string) $event->getInput(),
            'options' => $this->normalizeOptions($event->getInput()),
            'arguments' => $this->normalizeArguments($event->getInput()),
        ];

        if ($event->getCommand()) {
            $stopwatchEvent = $this->stopwatch->stop(spl_object_hash($event->getCommand()));

            $context['output'] = $this->getOutput($event->getCommand());
            $context['stopwatch'] = sprintf('%.2F MiB - %s', $stopwatchEvent->getMemory() / 1024 / 1024, $this->formatDuration($stopwatchEvent->getDuration()));
        }

        return $context;
    }

    /**
     * "supprime" les options quand c'est les options par défaut de SF
     * @return array<string, mixed>
     */
    private function normalizeOptions(InputInterface $input): array
    {
        return array_filter($input->getOptions(), function (mixed $value, string $name) {
//            if (in_array($name, ['ansi', 'help', 'quiet', 'profile', 'verbose', 'version', 'no-debug', 'no-interaction'])) {
//                return $name === 'ansi' ? $value !== null : $value !== false;
//            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeArguments(InputInterface $input): array
    {
        $arguments = $input->getArguments();

//        unset($arguments['command']);

        return $arguments;
    }

    private function getOutput(Command $command): ?string
    {
        if ($command instanceof LoggableOutputCommand) {
            $stream = $command->getLoggableOutput()->getStream();

            rewind($stream);

            return stream_get_contents($stream);
        }

        return null;
    }

    private function formatDuration(float $ms): string
    {
        // en ms
        if ($ms < 1000) {
            return $ms . ' ms';
        }

        // en minutes si on a dépassé 60 secondes
        if ($ms > 60000) {
            return round($ms / 60000, 2) . ' min';
        }

        return round($ms / 1000, 2) . ' s';
    }
}
