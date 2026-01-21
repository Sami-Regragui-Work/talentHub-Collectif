<?php

namespace App;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class View
{
    private static ?Environment $twig = null;

    public static function getTwig(): Environment
    {
        if (self::$twig === null) {
            $loader = new FilesystemLoader(__DIR__ . '/Views');
            self::$twig = new Environment(
                $loader,
                // [
                //     'cache' => false,
                //     'debug' => true,
                // ]
            );
        }

        return self::$twig;
    }

    public static function render(string $template, array $data = []): void
    {
        echo self::getTwig()->render($template, $data);
    }
}
