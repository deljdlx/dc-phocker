<?php
namespace Phocker\Traits;

use Phar;

Trait HasPhockerApi
{

    public function route_get_api_phocker_routes(): bool
    {
        $routes = $this->router->getRoutes();
        $data = [];

        foreach ($routes as $route) {
            $data[] = [
                'methods' => $route['methods'],
                'uri' => $route['uri'],
                'name' => $route['name'] ?? null,
            ];
        }

        return $this->jsonResponse($data);
    }

    public function route_get_api_phocker_version(): bool
    {
        $data = [
            'version' => '1.0.0',
            'is_phar' => $this->isPhar(),
            'bootstrap' => $this->getPharName(),
        ];

        if($this->isPhar()) {
            $phar = new Phar($this->rootDir);
            $data['signature'] = $phar->getSignature();
        }


        return $this->jsonResponse($data);
    }

    public function route_get_api_phocker_files(): bool
    {
        $files = $this->getFiles();
        $data = [];

        if(!$this->isPhar()) {
            $data = [
                'error' => 'Not running from a phar file'
            ];
            return $this->jsonResponse($data);
        };

        foreach ($files as $file) {
            $path = $file->getPath();
            $path = preg_replace('`.*?'.$this->getPharName() . '`', '', $path);

            $data[] = [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'path' => $path,
                'ctime' => $file->getCTime(),
            ];
        }

        return $this->jsonResponse($data);
    }
}