# Registering Services

FreshPress is using a Symfony like service container to manage dependencies.

To register a services you have to add it to the services.yml.
For example:
```yaml
    app.my_great_service:
        class: My\Namespace\GreatService
        arguments: ['an argument', '@some_other_service']
```

In your code you can load the service using the ServiceContainer:
```php
ServiceContainer::getInstance()->get('app.my_great_service');
```