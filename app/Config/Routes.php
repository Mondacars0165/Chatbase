<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('chatbot/clientes-ano', 'Chatbot::clientesAno');

$routes->get('/chat', 'Chat::index');
$routes->post('/chat/consultar', 'Chat::consultar');
