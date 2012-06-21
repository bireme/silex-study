<?php

// incluindo o Request
use Symfony\Component\HttpFoundation\Request;

$app->match('/', function (Request $request) use ($app, $DIR) {
   
    // pegando o parâmetro q
    $q = "";
    if($request->get("q")) {
        $q = $request->get("q");
    }

    // pegando o parâmetro page
    $page = 1;
    $start = 0;
    if($request->get("page")) {
        $page = $request->get("page") - 1;
        $start = $page * 20;
        $page++;
    }

    // construindo a url de consumo
    $url = "http://srv.bvsalud.org/iahx-controller/?site=bvsms&col=main&count=20&output=json&lang=pt&fl=20&op=search&sort=da%2Bdesc";
    $url .= "&q=$q";
    $url .= "&start=$start";
    $service = file_get_contents($url);
    $service = json_decode($service, true);

    // variáveis para criação da paginação
    $total = $service['diaServerResponse'][0]['response']['numFound'];
    $start = $service['diaServerResponse'][0]['response']['start'];

    // paginação
    $pages = $total / 20;
    $pagination = ($pages > 1) ? $pagination = range(1, $total / 20) : $pagination = array(1);

    // criando url para ser apendada na paginação
    $url = "?";
    foreach($request->query->all() as $key => $value) {
        if($key == 'page') continue;
        $url .= "&$key=$value";
    }

    // variáveis para o template
    $output = array();
    $output['docs'] = $service['diaServerResponse'][0]['response']['docs'];
    $output['total'] = $total;
    $output['page'] = $page;
    $output['pagination'] = $pagination;
    $output['url'] = $url;

    // "response" para o template
    return $app['twig']->render('results.html', $output);
});


?>