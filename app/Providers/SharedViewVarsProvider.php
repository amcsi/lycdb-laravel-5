<?php namespace Lycee\Providers;

use Illuminate\Support\ServiceProvider;

class SharedViewVarsProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
        /** @var \Illuminate\Contracts\View\Factory $view */
		$view = $this->app->make('view');

        $config = $this->app->make('config');
        $view->share('lyceeConfig', $config['lycee']);

        $view->share('shared', $config['view']['shared_vars']);
	}

}
