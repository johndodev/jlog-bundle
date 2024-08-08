<?php

declare(strict_types=1);

namespace Johndodev\JlogBundle\Monolog;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JlogHandler extends AbstractProcessingHandler
{
    private const ENDPOINT = 'https://jlog.dev/logs';

    private HttpClientInterface $httpClient;
    private string $projectApiKey;

    public function __construct(string $projectApiKey, HttpClientInterface $httpClient)
    {
        parent::__construct();

        if (!$projectApiKey) {
            throw new \Exception('JlogHandler: projectApiKey is required');
        }

        $this->httpClient = $httpClient;
        $this->projectApiKey = $projectApiKey;
    }

    public function handle(LogRecord $record): bool
    {
        throw new \Exception('This handler does not support handling a single record. Use buffer handler en amont.');
    }

    public function handleBatch(array $records): void
    {
        $this->send($this->getFormatter()->formatBatch($records));
    }

    protected function write(LogRecord $record): void
    {
        throw new \Exception('This handler does not support handling a single record. Use buffer handler en amont.');
    }

    protected function send($data): void
    {
        $this->httpClient->request('POST', self::ENDPOINT, [
            'headers' => [
                'X-API-KEY' => $this->projectApiKey,
                'Content-Type' => 'application/json',
            ],
            'body' => $data,
        ]);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new JsonFormatter(includeStacktraces: true);
    }
}
