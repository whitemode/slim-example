<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$categories = json_decode(file_get_contents("categories.json"), true);

$app->get('/', function ($request, $response) {
    $response->getBody()->write('Welcome to Slim!
                                <p><a href = "/categories">categories</a></p>
                                ');
    return $response;
});

$app->get('/categories', function ($request, $response) use ($categories) {
    $request = $request->getQueryParam('request');

    if ($request !== '') {
        $desiredCategories = array_filter($categories, fn($category) => mb_strpos($category["name"], $request) !== false);
        $params = [
            "request" => $request,
            "categories" => $desiredCategories
        ];
    } else {
        $params = [
            "request" => $request,
            "categories" => $categories
        ];
    }

    return $this->get('renderer')->render($response, 'categories/categories.phtml', $params);
});

$app->get('/categories/new', function ($request, $response) {
    $params = [
        'category' => ['id' => '', 'name' => ''],
        'error' => ''
    ];
    return $this->get('renderer')->render($response, "categories/new.phtml", $params);
});

$app->post('/categories', function ($request, $response) use ($categories) {
    $newCategory = $request->getParsedBodyParam('category');
    
    if (mb_strlen($newCategory['name']) !== 0) {
        $newId = count($categories) + 1;
        $newCategory['id'] = $newId;
        $categories[] = $newCategory;
        
        file_put_contents("categories.json", json_encode($categories));
        
        return $response->withRedirect('/categories', 302);
    }

    $params = [
        'category' => $newCategory,
        'error' => 'Название категории не может быть пустым'
    ];
    return $this->get('renderer')->render($response, "categories/new.phtml", $params);
});

$app->run();