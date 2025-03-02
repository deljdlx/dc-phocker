<?php
namespace Phocker;

class Router {
    private $routes = [];

    public function get(string $uri, callable $callback, string $name = null) {
        $this->addRoute(['GET'], $uri, $callback, $name);
    }

    public function post(string $uri, callable $callback, string $name = null) {
        $this->addRoute(['POST'], $uri, $callback, $name);
    }

    public function put(string $uri, callable $callback, string $name = null) {
        $this->addRoute(['PUT'], $uri, $callback, $name);
    }

    public function delete(string $uri, callable $callback, string $name = null) {
        $this->addRoute(['DELETE'], $uri, $callback, $name);
    }

    public function patch(string $uri, callable $callback, string $name = null) {
        $this->addRoute(['PATCH'], $uri, $callback, $name);
    }

    public function options(string $uri, callable $callback, string $name = null) {
        $this->addRoute(['OPTIONS'], $uri, $callback, $name);
    }

    public function any(string $uri, callable $callback, string $name = null) {
        $this->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'], $uri, $callback, $name);
    }

    public function addRoute(array $methods, string $uri, callable $callback, string $name = null) {
        if($name) {
            $this->routes[$name] = [
                'methods' => $methods,
                'uri' => $uri,
                'callback' => $callback
            ];
            return;
        }
        $this->routes[] = [
            'methods' => $methods,
            'uri' => $uri,
            'callback' => $callback
        ];
    }

    public function handleRequest($requestUri, $requestMethod) {
        foreach ($this->routes as $route) {
            if (in_array($requestMethod, $route['methods'])) {
                // Remplace tous les {param} par des groupes capturant
                $pattern = preg_replace('/{(\w+)}/', '(?P<$1>[^/]+)', $route['uri']);
                $pattern = '#^' . $pattern . '$#'; // Assure-toi que l'URL correspond parfaitement

                if (preg_match($pattern, $requestUri, $matches)) {
                    // On récupère les paramètres capturés
                    $parameters = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    $result = call_user_func_array($route['callback'], $parameters);
                    if ($result) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

}
