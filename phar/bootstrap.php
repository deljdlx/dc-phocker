<?php
include __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/src/Site.php';

$bootstrapFile = __FILE__;
if(Phar::running()) {
    $bootstrapFile = Phar::running();
}

$site = new Site(
    $bootstrapFile,
);


$method = null;
if(isset($_SERVER['REQUEST_METHOD'])) {
    $method = filter_var($_SERVER['REQUEST_METHOD']);
}

$uri = null;
if(isset($_SERVER['REQUEST_URI'])) {
    $uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
}

$site->run(
     (string) $method,
    (string) $uri
);
