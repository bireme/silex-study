<?php

require_once 'lib/class/dia.class.php';

use Symfony\Component\HttpFoundation\Request;

$app->match('/', function (Request $request) use ($app, $DEFAULT_PARAMS, $config) {
    
    $params = array_merge(
        $app['request']->request->all(),
        $app['request']->query->all()
    );

    $site = $DEFAULT_PARAMS['defaultSite'];
    if(isset($params['site']) and $params['site'] != "") {
        $site = $params['site'];
    } 

    $col = $DEFAULT_PARAMS['defaultCollection'];
    if(isset($params['col']) and $params['col'] != "") {
        $col = $params['col'];
    }

    $count = $config->documents_per_page;
    if(isset($params['count'])and $params['col'] != "") {
        $count = $params['count'];
    }

    $output = "site";
    if(isset($params['output']) and $params['output'] != "") {
        $output = $params['output'];
    }

    $lang = $DEFAULT_PARAMS['lang'];
    if(isset($params['lang']) and $params['lang'] != "") {
        $lang = $params['lang'];
    }

    $q = "";
    if(isset($params['q']) and $params['lang'] != "") {
        $q = $params['q'];
    }
    
    if(isset($params['sort']) and $params['sort'] != "") {
        //get sort field to apply
        $sort = getSortValue($col, $params['sort']);     
    } else {
        //get default sort
        $sort = getDefaultSort($col, $q);
    }

    $index = "";
    if(isset($params['index']) and $params['index'] != "") {
        $index = $params['index'];
    }

    $from = 0;
    if(isset($params['from']) and $params['from'] != "") {
        $from = $params['from'];
    }

    $filter_search = array();

    $dia = new Dia($site, $col, $count, $output, $lang);
    $dia_response = $dia->search($q, $index, $filter_search, $from);
    $response = json_decode($dia_response, true);

    $docs = $response['diaServerResponse'][0]['response']['docs'];

    $output_array = array();
    $output_array['docs'] = $docs;

    // print_r($docs);
    
    // output
    switch($output) {
        case "xml": case "sol":
            header("Content-type: text/xml");
            print $dia_response;
            break;
        default: 
            return $app['twig']->render('results.html', $output_array);
            break;
    }
    
});


?>