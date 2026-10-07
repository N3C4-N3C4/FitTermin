<?php
/**
 * Spisak svih ruta aplikacije.
 * @var Router $router
 */

// Početna
$router->get('/', 'HomeController@index');

// Prijava / registracija
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/registracija', 'AuthController@registerForm');
$router->post('/registracija', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');

// Treninzi (osnovni entitet – CRUD)
$router->get('/treninzi', 'TreningController@index');
$router->get('/treninzi/novi', 'TreningController@create');        // admin
$router->post('/treninzi', 'TreningController@store');             // admin
$router->get('/treninzi/{id}', 'TreningController@show');
$router->get('/treninzi/{id}/izmeni', 'TreningController@edit');   // admin
$router->post('/treninzi/{id}/izmeni', 'TreningController@update'); // admin
$router->post('/treninzi/{id}/obrisi', 'TreningController@destroy'); // admin

// Rezervacije korisnika
$router->get('/moje-rezervacije', 'RezervacijaController@index');

// Veb servisi (JSON API) – koriste se preko AJAX-a
$router->get('/api/treninzi', 'ApiController@treninzi');
$router->post('/api/rezervacije', 'ApiController@rezervisi');
$router->post('/api/rezervacije/{id}/otkazi', 'ApiController@otkazi');
$router->post('/api/treninzi/{id}/obrisi', 'ApiController@obrisiTrening');
$router->post('/api/upload', 'ApiController@upload');
