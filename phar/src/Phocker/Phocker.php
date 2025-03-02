<?php
namespace Phocker;

use Phar;
use PharFileInfo;
use RecursiveIteratorIterator;
use SQLite3;

abstract class Phocker
{
    protected string $bootstrapFile;
    protected string $pharName;
    protected string $currentDir;
    protected string $rootDir;

    abstract public function initialize();

    public function __construct(string $bootstrapFile)
    {
        $this->bootstrapFile = $bootstrapFile;
        $this->pharName = basename($bootstrapFile);
        $this->currentDir = getcwd();
        $this->rootDir = dirname($bootstrapFile);

        if($this->isPhar()) {
            $this->rootDir = $bootstrapFile;
        }
    }

    public function getPharFile(): string|false
    {
        if(!$this->isPhar()) {
            return false;
        }

        $file = $this->rootDir;
        $file = str_replace('phar://', '', $file);
        return $file;
    }

    public function isPhar(): bool
    {
        if (Phar::running() !== '') {
            return true;
        }

        return false;
    }

    /**
     * @return PharFileInfo[]
     */
    public function getFiles(): array
    {
        $phar = new Phar($this->getPharName());
        $files = [];
        foreach (new RecursiveIteratorIterator($phar) as $file) {
            $files[] = $file;
        }
        return $files;
    }

    public function getInformations()
    {
        echo "============================================" . PHP_EOL;
        echo 'Phar name: ' . $this->getPharName() . PHP_EOL;
        $files = $this->getFiles();
        echo 'Files: ' . count($files) . PHP_EOL;
        foreach ($files as $file) {
            echo "\t" . $file->getFilename() . PHP_EOL;
        }

        echo "============================================" . PHP_EOL;
    }


    public function getPharName(): string
    {
        return $this->pharName;
    }


    protected function addToPhar(string $file, string $alias = null)
    {
        $phar = new Phar($this->getPharName());
        $phar->addFile($file, $alias);
        $phar->stopBuffering();
    }

    public function phockMe(string $destination = null)
    {
        if (!$destination) {
            $destination = $this->getPharName();
        }

        // $this->initialize();
        $phar = new Phar($destination);
        $phar->buildFromDirectory($this->rootDir);
        $phar->setStub('<?php include "phar://".__FILE__. "/bootstrap.php"; __HALT_COMPILER(); ?>');
        $phar->stopBuffering();

        return;
    }

    public function unphockMe(string $destination)
    {
        $phar = new Phar($this->getPharName());
        $phar->extractTo($destination);
    }
}


