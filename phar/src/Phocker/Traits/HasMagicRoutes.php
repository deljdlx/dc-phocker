<?php
namespace Phocker\Traits;

Trait HasMagicRoutes
{
    public function registerMagicRoutes(): void
    {
        $this->router->any(
            '.*',
            function() {
                if(!isset($_SERVER['REQUEST_URI']) || !isset($_SERVER['REQUEST_METHOD'])) {
                    throw new \RuntimeException('Invalid request');
                }
                if(!is_string($_SERVER['REQUEST_URI']) || !is_string($_SERVER['REQUEST_METHOD'])) {
                    throw new \RuntimeException('Invalid request');
                }
                
                $uri = $_SERVER['REQUEST_URI'];
                $method = $_SERVER['REQUEST_METHOD'];
                $handled = $this->handleApiRoute($method, $uri);

                if(!$handled) {
                    $handled = $this->handlePageRoute($uri);
                }

                return $handled;
            }
        );
    }


    protected function displayPage(string $page): bool
    {
        $filepath = $this->pagesDir . '/' . $page . '.php';

        if(file_exists($filepath)) {
            ob_start();
            require_once $filepath;
            $content = ob_get_clean();
            ob_end_clean();
            if(!$content) {
                return false;
            }
            return $this->htmlResponse($content);
        }

        return false;
    }

    protected function handlePageRoute(string $uri): bool
    {
        $parts = explode('?', $uri);
        $uriParts = explode('/', $parts[0]);

        $filepath = $this->pagesDir . implode('/', $uriParts) . '.php';

        // security check if file is in pages directory
        $realpath = realpath($filepath);
        if(!$realpath) {
            return false;
        }

        if(strpos($realpath, $this->pagesDir) !== 0) {
            return false;
        }

        if(file_exists($filepath)) {
            ob_start();
            require_once $filepath;
            $content = ob_get_clean();
            ob_end_clean();
            if(!$content) {
                return false;
            }
            return $this->htmlResponse($content);
        }
        return false;
    }

    protected function handleApiRoute(string $method, string $uri): bool
    {
        $parts = explode('?', $uri);
        $uriParts = explode('/', $parts[0]);

        $call = 'route_' . $method . implode('_', $uriParts);

        if(method_exists($this, $call)) {
            return $this->$call();
        }
        return false;
    }
}
