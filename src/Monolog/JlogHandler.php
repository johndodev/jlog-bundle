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
    private const DEFAULT_ENDPOINT = 'https://jlog.io/logs';

    private HttpClientInterface $httpClient;
    private string $projectApiKey;
    private string $endpoint;

    public function __construct(string $projectApiKey, HttpClientInterface $httpClient, ?string $endpoint = null)
    {
        parent::__construct();

        if (!$projectApiKey) {
            throw new \Exception('JlogHandler: projectApiKey is required');
        }

        $this->httpClient = $httpClient;
        $this->projectApiKey = $projectApiKey;
        $this->endpoint = $endpoint ?? self::DEFAULT_ENDPOINT;
    }

    public function handle(LogRecord $record): bool
    {
        throw new \Exception('This handler does not support handling a single record. Use buffer handler en amont.');
    }

    public function handleBatch(array $records): void
    {
        // waiting https://github.com/Seldaek/monolog/pull/1906
//        $this->send($this->getFormatter()->formatBatch($records));

        $output = [];

        foreach ($records as $record) {
            $output[] = json_decode($this->getFormatter()->format($record), true);

            // pas besoin
            unset($output['level_name']);
        }

        $this->send($output);
    }

    protected function write(LogRecord $record): void
    {
        throw new \Exception('This handler does not support handling a single record. Use buffer handler en amont.');
    }

    protected function send($data): void
    {
        $this->httpClient->request('POST', $this->endpoint, [
            'headers' => [
                'X-API-KEY' => $this->projectApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
//            'body' => $data,
            'verify_peer' => $this->endpoint === self::DEFAULT_ENDPOINT,
        ]);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new JsonFormatter(includeStacktraces: true);
    }
}
