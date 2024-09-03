# Jlog Bundle

## Installation

```bash
composer require johndodev/jlog-bundle
```

## Configuration

### .env
```yaml
JLOG_API_KEY=your_api_key
```

### config/packages/jlog.yaml
```yaml
jlog:
    project_api_key: '%env(JLOG_API_KEY)%'
    enable_exception_listener: true
    ignore_exceptions:
        - Symfony\Component\HttpKernel\Exception\NotFoundHttpException
    # See chapter about logging commands
    console_channel: console
```

### monolog.yaml
You need to use the buffer handler en amont.  
See https://github.com/symfony/monolog-bundle/blob/master/DependencyInjection/Configuration.php#L139
for configuration options.  
Ex :
```yaml
monolog:
    channels:
        - deprecation # Deprecations are logged in the dedicated "deprecation" channel when it exists
        - command # same name as jlog.console_channel
    
    ...
    
    handlers:
        buffer:
            type: buffer
            handler: jlog
            level: debug # or whatever
            channels: ["app"]  # or whatever
            
        jlog:
            type: service
            id: jlog.monolog_handler
```

## Usage

### Logging commands
Extends `Johndodev\JlogBundle\Console\LoggableOutputCommand` in your command you want to log.  
The start and end of commands will be logged in Jlog, in the channel you configured, with the output as metadata.

### Logging exceptions
Unhandled exceptions are already logged (if enabled in config).
You can still manually log them as usual, with monolog : 
```php
try {
    // ...
} catch (\Exception $e) {
    $this->logger->error('lorem ipsum...', [
        'exception' => $e->getMessage()
    ]);
}
```
### Logging messages
With monologer, as usual.  
It is recommanded to use placeholder, especially for errors which triggers notifications, because the message is used for signature for unique notification.
    
```php
$this->logger->error('bla bla: {error}', [
    'error' => $e->getMessage()
]);
```
