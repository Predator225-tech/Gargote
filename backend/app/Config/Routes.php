<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * Toutes les routes de l'API sont préfixées par /api
 * et renvoient du JSON.
 */
$routes->get('/', 'Home::index');

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    // Catégories
    $routes->get('categories', 'Categories::index');

    // Plats (CRUD complet)
    $routes->get('plats', 'Plats::index');
    $routes->get('plats/(:num)', 'Plats::show/$1');
    $routes->post('plats', 'Plats::create');
    $routes->put('plats/(:num)', 'Plats::update/$1');
    $routes->delete('plats/(:num)', 'Plats::delete/$1');

    // Commandes
    $routes->get('commandes', 'Commandes::index');
    $routes->get('commandes/(:num)', 'Commandes::show/$1');
    $routes->post('commandes', 'Commandes::create');
});

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
 $routes->get('parametres','Parametres::index');
 $routes->get('serveurs','Serveurs::index');$routes->post('serveurs','Serveurs::create');$routes->put('serveurs/(:num)','Serveurs::update/$1');
 $routes->get('plat-du-jour','PlatDuJour::index');$routes->put('plat-du-jour','PlatDuJour::update');$routes->get('plat-du-jour/historique','PlatDuJour::historique');
 $routes->patch('commandes/(:num)/statut','Commandes::statut/$1');
 $routes->get('ingredients','Ingredients::index');$routes->get('ingredients/(:num)/mouvements','Ingredients::mouvements/$1');$routes->post('ingredients/(:num)/approvisionnement','Ingredients::approvisionnement/$1');
 $routes->get('statistiques','Statistiques::index');
});
