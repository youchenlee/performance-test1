<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/test', 'helloWorldHandler');
    $r->addRoute('GET', '/test2', 'helloWorldHandler2');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);



function helloWorldHandler() {
    $request = Request::createFromGlobals();
    $response = new Response(
        'Hello world!',
        Response::HTTP_OK,
        [
            'content-type' => 'text/html'
        ]
    );
    $response->prepare($request);
    $response->send();
}


function helloWorldHandler2() {
    $request_body = file_get_contents('php://input');
    http_response_code(200);
    header('content-type: text/html');
    echo "Hello World!";
    exit();
}

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        http_response_code(404);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        http_response_code(405);
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        // ... call $handler with $vars
        $handler();
        break;
}
