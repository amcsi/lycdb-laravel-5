<?php namespace Lycee\Providers;

use Illuminate\Support\ServiceProvider;
use Lycee\Console\Commands\InspireCommand;
use Lycee\Console\ImportCardsCommand;

class ArtisanServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app->bindShared('commands.inspire', function()
        {
            return new InspireCommand;
        });
        $this->commands('commands.inspire');

        $this->app->bindShared('commands.card', function()
        {
            return new ImportCardsCommand;
        });
	}

}
