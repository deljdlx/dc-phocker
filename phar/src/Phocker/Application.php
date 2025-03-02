<?php
namespace Phocker;

use Phar;
use SQLite3;

abstract class Application extends Phocker
{
    const PAGES_DIR = 'pages';

    public readonly Router $router;
    protected ?SQLite3 $database;
    protected $databaseName = 'data/phocker.db';
    protected string $databaseFile;

    public function __construct(string $root, string $pharName)
    {
        parent::__construct($root, $pharName);
        $this->router = new Router();
        $this->databaseFile = $this->currentDir . '/' . $this->databaseName;
        $this->database = null;
        $this->initializeRoutes();
    }

    public function route_get_api_phocker_version()
    {
        return $this->jsonResponse([
            'version' => '1.0.0',
            'is_phar' => $this->isPhar(),
            'phar_name' => $this->getPharName(),
        ]);
    }

    public function route_get_api_phocker_files()
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

        } elseif (isset($cmdOptions['i']) || isset($cmdOptions['info'])) {
            $this->getInformations();
            echo PHP_EOL;
        } elseif (isset($cmdOptions['u']) || isset($cmdOptions['unphar'])) {
            $currrentPath = getcwd();
            $this->unphockMe($currrentPath . '/phar');
        }
        elseif (isset($cmdOptions['init'])) {
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

    protected function displayPage($page)
    {
        $page = $this->sanitizePageName($page);
        if(is_file($this->rootDir . '/' . self::PAGES_DIR . '/' . $page . '.php')) {


            ob_start();
            require_once $this->rootDir . '/' . self::PAGES_DIR . '/' . $page . '.php';
            $content = ob_get_clean();
            ob_end_clean();

            return $this->htmlResponse($content);
        }
        return false;
    }

    public function sanitizePageName(string $page)
    {
        $page = str_replace(['/', '\\'], '', $page);
        $page = preg_replace('/\W/', '', $page);
        $page = str_replace('..', '', $page);

        return $page;
    }

    protected function initializeRoutes()
    {
        $this->router->get(
            '/assets/{type}/{file}',
            function($type, $file) {
                $this->getAsset($type . '/' . $file);
                return true;
            }
        );

        $this->router->get(
            '/download',
            function() {
                $this->downloadPhar();
                return true;
            }
        );

        $this->router->any(
            '.*',
            function() {
                $uri = $_SERVER['REQUEST_URI'];
                $method = $_SERVER['REQUEST_METHOD'];

                $parts = explode('?', $uri);
                $uriParts = explode('/', $parts[0]);

                $call = 'route_' . $method . implode('_', $uriParts);

                if(method_exists($this, $call)) {
                    return $this->$call();
                }
            }
        );
    }

    public function downloadPhar()
    {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $this->getPharName() . '"');
        readfile($this->getPharName());
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
            $mimeType = finfo_file($finfo, $this->pharRootDir . '/assets/' . $asset);
        }


        return $this->responseAsset($asset, $mimeType, $charset);
    }

    public function responseAsset($asset, $mimeType, $charset)
    {
        $buffer = file_get_contents($this->pharRootDir . '/assets/' . $asset);
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
