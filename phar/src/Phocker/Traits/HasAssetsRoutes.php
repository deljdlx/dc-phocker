<?php
namespace Phocker\Traits;

Trait HasAssetsRoutes
{
    protected function registerAssetsRoutes()
    {
        $this->router->get(
            '/assets/{type}/{file}',
            function($type, $file) {
                return $this->getAsset($type . '/' . $file);
            }
        );
    }

    public function getAsset(string $asset) {

        $mimeType = null;
        $charset = '';
        if(strpos($asset, 'css') !== false) {
            $mimeType = 'text/css';
            $charset = '; charset=utf-8';

        }
        elseif(strpos($asset, 'js') !== false) {
            $mimeType = 'application/javascript';
            $charset = '; charset=utf-8';
        }

        if(!$mimeType) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $this->assetsDir . '/' . $asset);
        }

        return $this->responseAsset($asset, $mimeType, $charset);
    }
}
