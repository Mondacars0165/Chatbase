<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('/chat', 'Chat::index');
$routes->post('/chat/consultar', 'Chat::consultar');
$routes->get('/chat/historial', 'Chat::historial');

