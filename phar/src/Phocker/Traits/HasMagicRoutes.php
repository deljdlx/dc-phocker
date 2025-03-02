<?php
namespace Phocker\Traits;

Trait HasMagicRoutes
{
    public function registerMagicRoutes()
    {
        $this->router->any(
            '.*',
            function() {
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


    protected function displayPage(string $page)
    {
        $filepath = $this->pagesDir . '/' . $page . '.php';

        if(file_exists($filepath)) {
            ob_start();
            require_once $filepath;
            $content = ob_get_clean();
            ob_end_clean();
            return $this->htmlResponse($content);
        }
    }

    protected function handlePageRoute($uri)
    {
        $parts = explode('?', $uri);
        $uriParts = explode('/', $parts[0]);

        $filepath = $this->pagesDir . implode('/', $uriParts) . '.php';

        // security check if file is in pages directory
        $realpath = realpath($filepath);
        if(strpos($realpath, $this->pagesDir) !== 0) {
            return false;
        }

        if(file_exists($filepath)) {
            ob_start();
            require_once $filepath;
            $content = ob_get_clean();
            ob_end_clean();
            return $this->htmlResponse($content);
        }
    }

    protected function handleApiRoute($method, $uri)
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
