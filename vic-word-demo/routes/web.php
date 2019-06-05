<?php

/** @var \Moon\Routing\Router $router */
$router = Moon::$app->get('router');

$router->get('/', 'IndexController::index');

$router->get('article', 'IndexController::articleList');
$router->get('article/add', 'IndexController::add');
$router->post('article/save', 'IndexController::save');

$router->get('article/word/{id}', 'IndexController::vicWord');
$router->get('article/es/{id}', 'IndexController::createEsIndexType');

$router->get('article/esi', 'IndexController::createEsIndex');