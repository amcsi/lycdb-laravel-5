<?php namespace Lycee\Console;

use Exception;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'Lycee\Console\Commands\InspireCommand',
		'Lycee\Console\ImportCardsCommand',
	];

	/**
	 * Run the console application.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @return int
	 */
	public function handle($input, $output = null)
	{
		try
		{
			return parent::handle($input, $output);
		}
		catch (Exception $e)
		{
			$this->reportException($e);

			$this->renderException($output, $e);

			return 1;
		}
	}

}
