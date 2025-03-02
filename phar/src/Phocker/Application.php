<?php
namespace Phocker;

use Phar;
use Phocker\Traits\HasAssetsRoutes;
use Phocker\Traits\HasMagicRoutes;
use Phocker\Traits\HasPhockerApi;
use SQLite3;

abstract class Application extends Phocker
{
    use HasAssetsRoutes;
    use HasPhockerApi;
    use HasMagicRoutes;

    const PAGES_DIR = 'pages';
    const ASSETS_DIR = 'assets';

    public readonly Router $router;

    protected ?SQLite3 $database;
    protected $databaseName = 'data/phocker.db';
    protected string $databaseFile;

    protected string $assetsDir;
    protected string $pagesDir;


    public function route_get_download()
    {
        if(!$this->isPhar()) {
            return false;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($this->getPharName()) . '"');
        readfile($this->getPharFile());
        return true;
    }

    public function __construct(string $root)
    {
        parent::__construct($root);
        $this->router = new Router();
        $this->databaseFile = $this->currentDir . '/' . $this->databaseName;
        $this->database = null;

        $this->assetsDir = $this->rootDir . '/' . self::ASSETS_DIR;
        $this->pagesDir = $this->rootDir . '/' . self::PAGES_DIR;

        $this->initializeRoutes();
    }

    public function run()
    {
        if (php_sapi_name() === 'cli') {
            $this->handleCli();
            return;
        }

        $this->handleRequest(
            $_SERVER['REQUEST_URI'],
            $_SERVER['REQUEST_METHOD']
        );
    }

    public function handleCli(){
        $cmdOptions = getopt('c:iu', [
            'create-phar:',
            'info',
            'init',
            'unphar'
        ]);

        if (isset($cmdOptions['c']) || isset($cmdOptions['create-phar'])) {

            $destination = $cmdOptions['c'] ?? $cmdOptions['create-phar'];

            if(!$destination) {
                $destination = getcwd() . '/phocker.phar';
            }

            $this->phockMe($destination);

        }

        if (isset($cmdOptions['i']) || isset($cmdOptions['info'])) {
            $this->getInformations();
            echo PHP_EOL;
        }

        if (isset($cmdOptions['u']) || isset($cmdOptions['unphar'])) {
            $currrentPath = getcwd();
            $this->unphockMe($currrentPath . '/phar');
        }

        if (isset($cmdOptions['init'])) {
            $this->initialize();
        }
    }

    public function handleRequest(?string $uri = null, string $method = null)
    {
        if (empty($uri)) {
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
        }
        if (empty($method)) {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        }

        return $this->router->handleRequest($uri, $method);
    }

    protected function initializeRoutes()
    {
        $this->registerAssetsRoutes();
        $this->registerMagicRoutes();
    }

    public function responseAsset($asset, $mimeType, $charset)
    {
        $buffer = file_get_contents($this->rootDir . '/assets/' . $asset);
        $headers = [
            'Content-Type' => $mimeType . $charset
        ];

        return $this->response($buffer, $headers);
    }


    public function jsonResponse($data)
    {
        return $this->response(
            json_encode($data),
            [
                'Content-Type' => 'application/json'
            ]
        );

        return true;
    }

    public function htmlResponse($data)
    {
        return $this->response(
            $data,
            [
                'Content-Type' => 'text/html; charset=utf-8'
            ]
        );
    }

    public function response($data, $headers = [])
    {
        foreach ($headers as $name => $header) {
            header($name . ': ' . $header);
        }
        header('X-is-phar: ' . (int) $this->isPhar());
        echo $data;

        return true;
    }

    public function __destruct()
    {
        $this->end();
    }

    private function saveDatabase()
    {
        $this->addToPhar('data/phocker.db', $this->databaseName);
    }

    private function end() {
        if(is_file($this->databaseFile)) {
            if($this->isPhar()) {
                $this->saveDatabase();
                // unlink($this->databaseFile);
            }
        }
        if($this->database) {
            $this->database->close();
        }
    }
}
