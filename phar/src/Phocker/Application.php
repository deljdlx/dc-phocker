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
    protected string $databaseName = 'data/phocker.db';
    protected string $databaseFile;

    protected string $assetsDir;
    protected string $pagesDir;


    public function route_get_download(): bool
    {
        if(!$this->isPhar()) {
            return false;
        }

        if(!$this->getPharFile()) {
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

    public function run(?string $method = null, ?string $uri = null): void
    {
        if (php_sapi_name() === 'cli') {
            $this->handleCli();
            return;
        }

        if(!isset($method) || !isset($uri)) {
            throw new \RuntimeException('Invalid request');
        }

        $this->handleRequest(
            $method,
            $uri,
        );
    }

    public function handleCli(): void
    {
        $cmdOptions = getopt('c:iu', [
            'create-phar:',
            'info',
            'init',
            'unphar'
        ]);

        if (isset($cmdOptions['c']) || isset($cmdOptions['create-phar'])) {

            $destination = $cmdOptions['c'] ?? $cmdOptions['create-phar'];

            if($destination === false) {
                $destination = getcwd() . '/phocker.phar';
            }

            if(!is_string($destination)) {
                throw new \RuntimeException('Invalid destination');
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

    public function handleRequest(string $method, string $uri): bool
    {
        return $this->router->handleRequest($method, $uri);
    }

    protected function initializeRoutes(): void
    {
        $this->registerAssetsRoutes();
        $this->registerMagicRoutes();
    }

    public function responseAsset(string $asset, string $mimeType, string $charset): bool
    {
        $buffer = file_get_contents($this->rootDir . '/assets/' . $asset);
        if ($buffer === false) {
            return false;
        }

        $headers = [
            'Content-Type' => $mimeType . $charset
        ];

        return $this->response($buffer, $headers);
    }


    public function jsonResponse(mixed $data): bool
    {
        if (!$data) {
            return false;
        }

        return $this->response(
            (string) json_encode($data),
            [
                'Content-Type' => 'application/json'
            ]
        );
    }

    public function htmlResponse(string $data): bool
    {
        return $this->response(
            $data,
            [
                'Content-Type' => 'text/html; charset=utf-8'
            ]
        );
    }

    /**
     * @param string $data
     * @param array<string, string> $headers
     * @return boolean
     */
    public function response(string $data, array $headers = []): bool
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

    private function saveDatabase(): void
    {
        $this->addToPhar('data/phocker.db', $this->databaseName);
    }

    private function end(): void
    {
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
