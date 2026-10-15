<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\Authenticate;
use App\Middleware\RedirectIfAuthenticated;
use App\Middleware\RequirePermission;
use App\Middleware\SecurityHeaders;
use App\Middleware\SetLocale;
use App\Middleware\StartSession;
use App\Middleware\VerifyCsrfToken;
use Throwable;

/**
 * Application kernel and minimal service container.
 */
final class App
{
    private static ?App $instance = null;

    private array $services = [];
    private array $factories = [];
    public readonly Router $router;

    private array $globalMiddleware = [
        SecurityHeaders::class,
        StartSession::class,
        SetLocale::class,
        VerifyCsrfToken::class,
    ];

    private array $aliases = [
        'auth' => Authenticate::class,
        'guest' => RedirectIfAuthenticated::class,
        'can' => RequirePermission::class,
    ];

    public function __construct(public readonly string $basePath, public readonly bool $testing = false)
    {
        self::$instance = $this;
        // Earlier files win, so test overrides are loaded before .env.
        if ($testing) {
            Env::load($basePath . '/.env.testing');
        }
        Env::load($basePath . '/.env');

        $this->singleton('config', fn () => new Config($basePath . '/config'));
        $this->singleton('db', fn () => new Database($this->config()->get('database')));
        $this->singleton('session', fn () => new Session($this->config()->get('session'), !$testing));
        $this->singleton('auth', fn () => new Auth($this->get('db'), $this->get('session')));
        $this->singleton('translator', fn () => new Translator($basePath . '/lang'));
        $this->singleton('view', fn () => new View($basePath . '/resources/views'));
        $this->singleton('logger', fn () => new Logger($basePath . '/storage/logs'));
        $this->singleton('limiter', fn () => new RateLimiter($this->get('db')));
        $this->singleton('mailer', fn () => new Mailer($this->config()->get('mail'), $this->get('view'), $this->get('logger')));

        $this->router = new Router();
        $router = $this->router;
        require $basePath . '/routes/web.php';

        date_default_timezone_set('UTC');
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Application has not been bootstrapped.');
        }
        return self::$instance;
    }

    public function singleton(string $name, callable $factory): void
    {
        $this->factories[$name] = $factory;
        unset($this->services[$name]);
    }

    public function instanceOf(string $name, mixed $service): void
    {
        $this->services[$name] = $service;
    }

    public function get(string $name): mixed
    {
        if (!array_key_exists($name, $this->services)) {
            if (!isset($this->factories[$name])) {
                throw new \RuntimeException("Service [$name] is not registered.");
            }
            $this->services[$name] = ($this->factories[$name])();
        }
        return $this->services[$name];
    }

    public function config(): Config
    {
        return $this->get('config');
    }

    public function handle(Request $request): Response
    {
        $this->instanceOf('request', $request);
        try {
            $methodNotAllowed = false;
            $match = $this->router->match($request->method, $request->path, $methodNotAllowed);
            $routeMiddleware = [];
            $action = null;
            if ($match !== null) {
                [$route, $params] = $match;
                $request->setRouteParams($params);
                $request->setAttribute('route', $route);
                $routeMiddleware = $route['middleware'];
                $action = $route['action'];
            }

            $pipeline = array_merge(
                array_map(fn ($m) => [$m, null], $this->globalMiddleware),
                array_map(fn ($m) => $this->resolveMiddleware($m), $routeMiddleware)
            );

            $core = function (Request $request) use ($action, $match, $methodNotAllowed): Response {
                if ($match === null) {
                    throw new HttpException($methodNotAllowed ? 405 : 404);
                }
                return $this->callAction($action, $request);
            };

            $next = array_reduce(
                array_reverse($pipeline),
                fn (callable $next, array $mw) => function (Request $request) use ($mw, $next): Response {
                    [$class, $arg] = $mw;
                    return (new $class())->handle($request, $next, $arg);
                },
                $core
            );

            return $next($request);
        } catch (Throwable $e) {
            return $this->renderException($e, $request);
        }
    }

    private function resolveMiddleware(string $definition): array
    {
        [$name, $arg] = array_pad(explode(':', $definition, 2), 2, null);
        $class = $this->aliases[$name] ?? $name;
        return [$class, $arg];
    }

    private function callAction(array|callable $action, Request $request): Response
    {
        if (is_array($action)) {
            [$class, $method] = $action;
            $result = (new $class())->{$method}($request);
        } else {
            $result = $action($request);
        }
        if ($result instanceof Response) {
            return $result;
        }
        return Response::html((string) $result);
    }

    public function renderException(Throwable $e, Request $request): Response
    {
        if ($e instanceof ValidationException) {
            if ($request->wantsJson()) {
                return Response::json(['message' => __('validation.failed'), 'errors' => $e->errors], 422);
            }
            $session = $this->get('session');
            $session->flash('errors', $e->errors);
            $session->flash('old', $e->old);
            return (new SecurityHeaders())->apply(Response::redirect(previous_url()));
        }

        $status = $e instanceof HttpException ? $e->status : 500;
        if ($status === 500) {
            $this->get('logger')->error($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'path' => $request->path,
            ]);
            if ($this->testing && Env::get('TEST_RETHROW', false)) {
                throw $e;
            }
        }

        if ($request->wantsJson()) {
            $response = Response::json(['message' => __('error.' . $status . '.title')], $status);
        } else {
            try {
                $html = $this->get('view')->render('errors/error', [
                    'status' => $status,
                    'debug' => $status === 500 && $this->config()->get('app.debug') ? $e : null,
                    'meta' => ['title' => __('error.' . $status . '.title'), 'robots' => 'noindex'],
                ]);
            } catch (Throwable $inner) {
                $this->get('logger')->error('Error page rendering failed', ['error' => $inner->getMessage()]);
                $html = '<!doctype html><title>Error</title><h1>' . $status . '</h1>';
            }
            $response = Response::html($html, $status);
        }
        return (new SecurityHeaders())->apply($response);
    }
}
