<?php

namespace App;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class View
{
    private static ?Environment $twig = null;

    private static function getTwig(): Environment
    {
        if (self::$twig === null) {
            $loader = new FilesystemLoader(__DIR__ . '/Views');
            self::$twig = new Environment($loader, [
                'cache' => false, // Set to __DIR__ . '/cache/twig' in production
                'auto_reload' => true,
            ]);
        }
        return self::$twig;
    }

    public static function render(string $template, array $data = [], int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: text/html; charset=UTF-8');
        echo self::getTwig()->render($template, $data);
        exit;
    }
}