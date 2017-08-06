# Using TWIG to create themes

You can create your theme traditionally but you can also use the TWIG template engine.

To use twig in your template, you have to load the twig-service from the Service Container.
Pass in the load method the directory-name of your template + the path to the template-file.

Example:
In your Theme in your index.php:

```php
<?php

use Devtronic\FreshPress\DependencyInjection\ServiceContainer;

/** @var Twig_Environment $twig */
$twig = ServiceContainer::getInstance()->get('twig');

// Load the index.html.twig from the TwentySeventeen Template
$view = $twig->load('twentyseventeen/views/index.html.twig');
echo $view->render([]);
```