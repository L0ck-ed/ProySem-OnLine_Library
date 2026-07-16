<?php

declare(strict_types=1);

namespace App\Core;

use App\Helpers\Csrf;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (!is_file($viewPath)) {
            throw new HttpException('Vista no encontrada: ' . $view, 500);
        }

        ob_start();
        require $viewPath;
        $html = (string) ob_get_clean();

        $tokenInput = Csrf::input();
        $html = preg_replace_callback(
            '/<form\b([^>]*)>/i',
            static function (array $match) use ($tokenInput): string {
                $atributos = $match[1] ?? '';
                if (!preg_match('/\bmethod\s*=\s*["\']?post["\']?/i', $atributos)) {
                    return $match[0];
                }
                return $match[0] . $tokenInput;
            },
            $html,
        ) ?? $html;

        echo $html;
    }
}
