<?php

use Interfaces\ValidationServiceInterface;
use Services\ValidationService;

class Router
{
    private $routes = [];
    private $dependencyMap = [];

    public function __construct()
    {
        $this->dependencyMap = [
            ValidationServiceInterface::class => ValidationService::class,
        ];
    }

    public function addRoute($method, $path, $controller, $action)
    {
        $path = preg_replace('/\{(\w+)\}/', '(?P<\1>[^/]+)', $path);
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
                $controllerClass = $route['controller'];
                $action = $route['action'];

                // Filter only named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Resolve controller dependencies
                $controller = $this->resolveController($controllerClass);

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

    private function resolveController(string $controllerClass)
    {
        $reflectionClass = new ReflectionClass($controllerClass);
        $constructor = $reflectionClass->getConstructor();

        if (is_null($constructor)) {
            // If no constructor, just instantiate the controller without dependencies
            return new $controllerClass;
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $dependencyClass = $parameter->getClass();
            if ($dependencyClass) {
                $dependencies[] = $this->resolveDependency($dependencyClass->name);
            } else {
                throw new Exception("Cannot resolve the dependency for parameter {$parameter->name}");
            }
        }

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    private function resolveDependency(string $dependencyClass)
    {
        // Check if the dependency is an interface and resolve it to a concrete class
        if (isset($this->dependencyMap[$dependencyClass])) {
            $dependencyClass = $this->dependencyMap[$dependencyClass];
        }

        if (!class_exists($dependencyClass)) {
            throw new Exception("Cannot resolve dependency: $dependencyClass");
        }

        // Recursively resolve dependencies of the dependency
        $reflectionClass = new ReflectionClass($dependencyClass);
        $constructor = $reflectionClass->getConstructor();

        if (is_null($constructor)) {
            // If no constructor, just instantiate the class without dependencies
            return new $dependencyClass;
        }

        $constructorParams = $constructor->getParameters();
        $constructorArgs = [];

        foreach ($constructorParams as $param) {
            $paramClass = $param->getClass();
            if ($paramClass) {
                $constructorArgs[] = $this->resolveDependency($paramClass->name);
            } else {
                throw new Exception("Cannot resolve the dependency for parameter {$param->name}");
            }
        }

        return $reflectionClass->newInstanceArgs($constructorArgs);
    }
}