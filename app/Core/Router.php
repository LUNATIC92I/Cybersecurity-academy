<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];
    private array $named = [];
    private array $groupStack = [];

    public function get(string $path, array|callable $action, ?string $name = null): self
    {
        return $this->add(['GET', 'HEAD'], $path, $action, $name);
    }

    public function post(string $path, array|callable $action, ?string $name = null): self
    {
        return $this->add(['POST'], $path, $action, $name);
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function add(array $methods, string $path, array|callable $action, ?string $name): self
    {
        $prefix = '';
        $middleware = [];
        foreach ($this->groupStack as $group) {
            $prefix .= rtrim($group['prefix'] ?? '', '/');
            $middleware = array_merge($middleware, (array) ($group['middleware'] ?? []));
        }
        $full = '/' . trim($prefix . '/' . trim($path, '/'), '/');
        // The constraint may itself contain {n} or {n,m} quantifiers, so the
        // constraint sub-pattern allows balanced {digits[,digits]} groups.
        $regex = '#^' . preg_replace_callback(
            '/\{([a-zA-Z_]+)(?::((?:[^{}]|\{\d+(?:,\d*)?\})+))?\}/',
            fn ($m) => '(?P<' . $m[1] . '>' . (($m[2] ?? '') !== '' ? $m[2] : '[^/]+') . ')',
            $full
        ) . '$#u';

        $route = [
            'methods' => $methods,
            'path' => $full,
            'regex' => $regex,
            'action' => $action,
            'middleware' => $middleware,
            'name' => $name,
        ];
        $this->routes[] = $route;
        if ($name !== null) {
            $this->named[$name] = $full;
        }
        return $this;
    }

    /**
     * @return array{0: array, 1: array}|null  [route, params] or null
     */
    public function match(string $method, string $path, bool &$methodNotAllowed = false): ?array
    {
        $methodNotAllowed = false;
        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            if (!in_array($method, $route['methods'], true)) {
                $methodNotAllowed = true;
                continue;
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return [$route, $params];
        }
        return null;
    }

    public function url(string $name, array $params = []): string
    {
        if (!isset($this->named[$name])) {
            throw new \InvalidArgumentException("Unknown route [$name].");
        }
        $path = preg_replace_callback(
            '/\{([a-zA-Z_]+)(?::(?:[^{}]|\{\d+(?:,\d*)?\})+)?\}/',
            function ($m) use (&$params, $name) {
                if (!array_key_exists($m[1], $params)) {
                    throw new \InvalidArgumentException("Missing parameter [{$m[1]}] for route [$name].");
                }
                $value = rawurlencode((string) $params[$m[1]]);
                unset($params[$m[1]]);
                return $value;
            },
            $this->named[$name]
        );
        if ($params) {
            $path .= '?' . http_build_query($params);
        }
        return $path;
    }

    public function routes(): array
    {
        return $this->routes;
    }
}
