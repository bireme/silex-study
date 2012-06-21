<?php

require_once __DIR__ . '/silex/vendor/autoload.php';

// diretórios 
$DIR['root'] = __DIR__ . "/";
$DIR['template'] = $DIR['root'] . "template/";
$DIR['views'] = $DIR['root'] . "views/";

// iniciando o silex
$app = new Silex\Application();

// ativando o debug, caso haja erros
$app['debug'] = true;

// iniciando o twig, buscando templates em /template
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => $DIR['template'],
));

// incluindo controllers
require $DIR['root'] . "urls.php";

$app->run();

?>