<?php
include __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/src/Site.php';

$site = new Site(__DIR__, 'phocker.phar');
$site->run();

return;

if(isset($_SERVER['REQUEST_URI'])) {
    $site->handleRequest(
        $_SERVER['REQUEST_URI'],
        $_SERVER['REQUEST_METHOD']
    );
}

// ===========================================================

$cmdOptions = getopt('ciu', [
    'create-phar',
    'info',
    'init',
    'unphar'
]);


if (isset($cmdOptions['c']) || isset($cmdOptions['create-phar'])) {
    $site->phockMe();
}
elseif(isset($cmdOptions['i']) || isset($cmdOptions['info'])) {
    $site->getInformations();
    echo PHP_EOL;
}
elseif(isset($cmdOptions['u']) || isset($cmdOptions['unphar'])) {
    $currrentPath = getcwd();
    $site->unphockMe($currrentPath . '/phar');
}
return;

// ===========================================================


