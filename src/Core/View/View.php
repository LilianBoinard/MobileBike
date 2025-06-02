<?php

namespace MobileBike\Core\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class View
{
    private static ?Environment $twig = null;

    private static function getTwig(): Environment
    {
        if (self::$twig === null) {
            $loader = new FilesystemLoader(dirname(__DIR__, 2) . '/App/View');
            self::$twig = new Environment($loader);
        }
        return self::$twig;
    }

    public static function twig(string $template, array $data = [], array $options = []): string
    {
        $loader = new FilesystemLoader(dirname(__DIR__, 2) . '/App/View');
        $twig = new Environment($loader, $options);
        return $twig->render($template, $data);
    }
}
