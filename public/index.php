<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response) : Response {
    $indexFile = __DIR__. '/../resources/views/index.html';
    $html = file_get_contents($indexFile);
    $response->getBody()->write($html);
    return $response;
});

$app->get('/he/all', function (Request $request, Response $response) : Response {
    $listFile = __DIR__. '/../resources/heList.json';
    $json = file_get_contents($listFile);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

$app->get('/he/best', function (Request $request, Response $response) : Response {
    $bestFile = __DIR__. '/../resources/heBest.json';
    $json = file_get_contents($bestFile);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

$app->get('/hivesql', function (Request $request, Response $response) : Response {
    $hiveSqlFile = __DIR__ . '/../resources/hiveSql.json';
    $json = file_get_contents($hiveSqlFile);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

$app->run();
