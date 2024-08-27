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

```

### monolog.yaml
You need to use the buffer handler en amont.  
See https://github.com/symfony/monolog-bundle/blob/master/DependencyInjection/Configuration.php#L139
for configuration options.  
Ex :
```yaml
monolog:
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
