<?php
class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler): void  { $this->add('GET',  $path, $handler); }
    public function post(string $path, array|callable $handler): void { $this->add('POST', $path, $handler); }

    private function add(string $method, string $path, array|callable $handler): void
    {
        $pattern = preg_replace('#:([a-zA-Z_]+)#', '(?P<$1>[^/]+)', $path);
        $this->routes[] = ['method' => $method, 'pattern' => "#^{$pattern}$#", 'handler' => $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/') ?: '/';
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            if (preg_match($route['pattern'], $uri, $m)) {
                $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                $h = $route['handler'];
                if (is_callable($h)) { $h($params); return; }
                [$class, $action] = $h;
                (new $class())->$action($params);
                return;
            }
        }
        http_response_code(404);
        require BASE_PATH . '/app/views/shared/404.php';
    }
}