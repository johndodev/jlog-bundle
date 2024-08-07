<?php

declare(strict_types=1);

namespace Johndodev\JlogBundle\Monolog;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JlogHandler extends AbstractProcessingHandler
{
    private HttpClientInterface $httpClient;
    private string $projectApiKey;
    private string $endpoint;

    public function __construct(#[Autowire(env: 'JLOG_API_KEY')] string $projectApiKey, #[Autowire(env: 'JLOG_ENDPOINT')] string $endpoint, HttpClientInterface $httpClient, int|string|Level $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->httpClient = $httpClient;
        $this->projectApiKey = $projectApiKey;
        $this->endpoint = $endpoint;
    }

    public function handle(LogRecord $record): bool
    {
        throw new \Exception('This handler does not support handling a single record. Use buffer handler en amont.');
    }

    public function handleBatch(array $records): void
    {
        $level = $this->level;

        $records = array_filter($records, function ($record) use ($level) {
            return $record->level->value >= $level->value;
        });

        if ($records) {
            $this->send($this->getFormatter()->formatBatch($records));
        }
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
            'body' => $data,
            'verify_host' => false,
            'verify_peer' => false,
        ]);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new JsonFormatter(includeStacktraces: true);
    }
}
