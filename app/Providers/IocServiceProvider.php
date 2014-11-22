<?php namespace Lycee\Providers;

use Illuminate\Support\ServiceProvider;

class IocServiceProvider extends ServiceProvider {

    public function register()
    {
        $app = $this->app;

        $app->singleton('consoleLogger', function ($app) {
            $logger = new \Monolog\Logger('Console logger');
            $handler = new \Monolog\Handler\StreamHandler('php://stdout');
            $logger->pushHandler($handler);

            return $logger;
        });

        $app->singleton('Lycee\Importer\Lycee\Importer', function ($app) {
            $consoleLogger = $app->make('consoleLogger');
            $params = [
                'logger' => $consoleLogger,
            ];
            return $app->build('Lycee\Importer\Lycee\Importer', $params);
        });

        $app->singleton('Lycee\Zend\CacheHelper', function ($app) {
            $cachePath = storage_path() . '/framework/cache/lycee-tcg.com';
            $storageParams = [
                'directory' => $cachePath,
            ];
            $storage = $app->make('Illuminate\Cache\FileStore', $storageParams);

            $cache = new \Illuminate\Cache\Repository($storage);

            $defaultMinutes = 60 * 24 * 365 * 10; // 10 years
            $cache->setDefaultCacheTime($defaultMinutes);

            $cacheHelper = new \Lycee\Zend\CacheHelper($cache);

            return $cacheHelper;
        });

        $app->singleton('AMysql', function ($app) {
            $config = $app->make('config');

            $connDetails = $config->get('database.connections.mysql');
            $connDetails['driver'] = 'mysqli'; // force mysqli
            $connDetails['db'] = $connDetails['database'];
            $connDetails['db'] = $connDetails['database'];

            $amysql = new \AMysql($connDetails);
            $amysql->setUtf8();

            return $amysql;
        });
    }
}
