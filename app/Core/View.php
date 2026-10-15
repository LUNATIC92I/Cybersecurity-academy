<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Plain-PHP template renderer. Templates must escape every dynamic value
 * with e(); raw output is only used for content rendered by ContentRenderer,
 * which escapes HTML itself.
 */
final class View
{
    private array $shared = [];

    public function __construct(private readonly string $basePath)
    {
    }

    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    public function render(string $view, array $data = [], ?string $layout = 'layouts/app'): string
    {
        $content = $this->renderFile($view, $data);
        if ($layout === null) {
            return $content;
        }
        return $this->renderFile($layout, array_merge($data, ['content' => $content]));
    }

    public function partial(string $view, array $data = []): string
    {
        return $this->renderFile($view, $data);
    }

    private function renderFile(string $view, array $data): string
    {
        $file = $this->basePath . '/' . str_replace('.', '/', $view) . '.php';
        if (!preg_match('#^[a-zA-Z0-9_/\-.]+$#', $view) || !is_file($file)) {
            throw new \RuntimeException("View [$view] not found.");
        }
        $data = array_merge($this->shared, $data);
        $render = static function (string $__file, array $__data): string {
            extract($__data, EXTR_SKIP);
            ob_start();
            try {
                include $__file;
            } catch (\Throwable $e) {
                ob_end_clean();
                throw $e;
            }
            return (string) ob_get_clean();
        };
        return $render($file, $data);
    }
}
