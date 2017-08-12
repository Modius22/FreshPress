<?php

namespace Devtronic\FreshPress\Core\Twig;

class CoreExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('trans', [$this, 'translate']),
            new \Twig_SimpleFilter('transx', [$this, 'translateX']),
        ];
    }

    public function translate($text, $domain = 'default')
    {
        return translate($text, $domain);
    }

    public function translateX($text, $context, $domain = 'default')
    {
        return translate_with_gettext_context($text, $context, $domain);
    }
}
