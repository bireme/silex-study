<?php

// incluindo o Request
use Symfony\Component\HttpFoundation\Request;

$app->match('/', function (Request $request) use ($app, $DIR) {
    require $DIR['views'] . "ids.php";

    // "response" para o template
    return $app['twig']->render('results.html', $output);
});

?>