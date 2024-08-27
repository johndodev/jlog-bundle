<?php

declare(strict_types=1);

namespace Johndodev\JlogBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Logs exceptions
 */
class ExceptionListener
{
    private LoggerInterface $logger;
    private array $ignoredExeptions;

    public function __construct(LoggerInterface $logger, array $ignoreExceptions = [])
    {
        $this->logger = $logger;
        $this->ignoredExeptions = $ignoreExceptions;
    }

    public function __invoke(ExceptionEvent $event): void
    {
        foreach ($this->ignoredExeptions as $ignoredExeption) {
            if ($event->getThrowable() instanceof $ignoredExeption) {
                return;
            }
        }

        $this->logger->error($event->getThrowable()->getMessage(), ['exception' => $event->getThrowable()]);
    }
}
