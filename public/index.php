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

$companiesStr = "Автобизнес0Бытовые услуги, сервис, ЖКХ0Государство и общество0Здоровье, медицина, красота0Кадровые и рекрутинговые компании0Культура и искусство0Мебель, предметы интерьера0Медицинские учреждения0Недвижимость0Нефтегазовая промышленность0Образование0Оборудование, сырье, материалы0Отдых, спорт, развлечения0Охрана/Безопасность0Продукты питания0Реклама, полиграфия, СМИ0Строительство и ремонт0Телекоммуникации, связь, интернет0Техника для дома и офиса0Торговля0Транспорт и перевозки0Услуги для бизнеса0Финансы, страхование, инвестиции0Экстренные и справочные службы0Юридические, нотариальные, оценочные услуги";

$companies = explode("0", $companiesStr);

$app->get('/', function ($request, $response) {
    $response->getBody()->write('Welcome to Slim!
                                <p><a href = "/companies">companies</a></p>
                                ');
    return $response;
});

$app->get('/companies', function ($request, $response) use ($companies) {
    $request = $request->getQueryParam('request', '');

    if ($request !== '') {
        $desiredCompanies = array_filter($companies, fn($company) => mb_strpos($company, $request) !== false);
        
        $params = [
            "request" => $request,
            "companies" => $desiredCompanies
        ];
    } else {
        $params = [
            "request" => $request,
            "companies" => $companies
        ];
    }

    return $this->get('renderer')->render($response, 'companies/companies.phtml', $params);
});

$app->get('/companies/{id}', function ($request, $response, array $args) use ($companies) {
    $id = $args['id'];
    if (!array_key_exists($id-1, $companies)) {
        return $response->withStatus(404);
    }
    return $response->write($companies[$id-1]);
});

$app->run();