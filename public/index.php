<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

session_start();

$container = new Container();

$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container);
$app = AppFactory::create();
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
    $messages = $this->get('flash')->getMessages();

    if ($request !== '') {
        $desiredCategories = array_filter($categories, fn($category) => mb_strpos($category["name"], $request) !== false);
        $params = [
            "request" => $request,
            "categories" => $desiredCategories,
            "flash" => $messages
        ];
    } else {
        $params = [
            "request" => $request,
            "categories" => $categories,
            "flash" => $messages
        ];
    }

    //print_r($params['flash']['success'][0]);

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
        
        $this->get('flash')->addMessage('success', 'Категория добавлена успешно!');

        return $response->withRedirect('/categories', 302);
    }

    $params = [
        'category' => $newCategory,
        'error' => 'Название категории не может быть пустым'
    ];
    return $this->get('renderer')->render($response, "categories/new.phtml", $params);
});

$app->get('/foo', function ($req, $res) {
    // Добавление флеш-сообщения. Оно станет доступным на следующий HTTP-запрос.
    // 'success' — тип флеш-сообщения. Используется при выводе для форматирования.
    // Например можно ввести тип success и отражать его зелёным цветом (на Хекслете такого много)
    $this->get('flash')->addMessage('success', 'This is a message');

    return $res->withRedirect('/bar');
});

$app->get('/bar', function ($req, $res, $args) {
    // Извлечение flash сообщений установленных на предыдущем запросе
    $messages = $this->get('flash')->getMessages();
    var_dump($_REQUEST);
    echo "\n";
    print_r($messages); // => ['success' => ['This is a message']]

    $params = ['flash' => $messages];
    $this->get('renderer')->render($res, 'bar.phtml', $params);
});

$app->run();