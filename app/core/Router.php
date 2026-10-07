<?php
/**
 * Jednostavan ruter: povezuje HTTP metodu + putanju sa metodom kontrolera.
 * Primer: $router->get('/treninzi/{id}', 'TreningController@show');
 */
class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, string $handler): void
    {
        // {id} se pretvara u imenovanu grupu koja prihvata samo cifre
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', $path) . '$#';
        $this->routes[] = [$method, $regex, $handler];
    }

    public function dispatch(string $method, string $path): void
    {
        $pathExists = false;

        foreach ($this->routes as [$routeMethod, $regex, $handler]) {
            if (!preg_match($regex, $path, $matches)) {
                continue;
            }
            if ($routeMethod !== $method) {
                $pathExists = true;
                continue;
            }

            [$controllerName, $action] = explode('@', $handler);
            $params = array_map('intval', array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));

            $controller = new $controllerName();
            $controller->$action(...array_values($params));
            return;
        }

        abort($pathExists ? 405 : 404);
    }
}
