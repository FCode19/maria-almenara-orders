<?php

namespace App\Helpers;

class View
{
    public static function render(
        string $view,
        array $data = []
    ): void {
        $path = BASE_PATH
            . '/app/Views/'
            . str_replace('.', '/', $view)
            . '.php';

        if (!file_exists($path)) {
            throw new \RuntimeException(
                'Vista no encontrada: ' . $view
            );
        }

        extract($data, EXTR_SKIP);

        require $path;
    }
}