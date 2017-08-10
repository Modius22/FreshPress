<?php

namespace Devtronic\FreshPress\Core\Twig;

class CoreExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('trans', [$this, 'translate']),
        ];
    }

    public function translate($text, $domain = 'default')
    {
        return translate($text, $domain);
    }
}