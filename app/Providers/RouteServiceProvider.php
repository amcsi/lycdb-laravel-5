<?php namespace Lycee\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider {

	/**
	 * All of the application's route middleware keys.
	 *
	 * @var array
	 */
	protected $middleware = [
		'auth' => 'Lycee\Http\Middleware\Authenticated',
		'auth.basic' => 'Lycee\Http\Middleware\AuthenticatedWithBasicAuth',
		'csrf' => 'Lycee\Http\Middleware\CsrfTokenIsValid',
		'guest' => 'Lycee\Http\Middleware\IsGuest',
	];

	/**
	 * Called before routes are registered.
	 *
	 * Register any model bindings or pattern based filters.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function before(Router $router)
	{
		//
	}

	/**
	 * Define the routes for the application.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function map(Router $router)
	{
		$router->group(['namespace' => 'Lycee\Http\Controllers'], function($router)
		{
			require app_path('Http/routes.php');
		});
	}

}
