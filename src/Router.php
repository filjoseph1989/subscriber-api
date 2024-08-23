<?php

class Router
{
    private $routes = [];

    public function addRoute($method, $path, $controller, $action)
    {
        $path = preg_replace('/\{(\w+)\}/', '(?P<\1>[^/]+)?', $path);
        $path = '#^' . $path . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
        ];
    }

    public function dispatch($requestUri, $requestMethod)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['path'], $requestUri, $matches)) {
                $controller = new $route['controller'];
                $action = $route['action'];

                // Filter only named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Provide default values if parameters are missing
                $reflection = new ReflectionMethod($controller, $action);
                $reflectionParams = $reflection->getParameters();
                foreach ($reflectionParams as $param) {
                    if (!isset($params[$param->getName()]) && $param->isDefaultValueAvailable()) {
                        $params[$param->getName()] = $param->getDefaultValue();
                    }
                }

                if (method_exists($controller, $action)) {
                    return $controller->$action(...array_values($params));
                } else {
                    http_response_code(404);
                    echo "Method $action not found in controller " . get_class($controller);
                    return;
                }
            }
        }

        http_response_code(404);
        echo "No route matched.";
    }
}