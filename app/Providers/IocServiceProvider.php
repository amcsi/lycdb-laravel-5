<?php namespace Lycee\Providers;

use Illuminate\Support\ServiceProvider;

class IocServiceProvider extends ServiceProvider {

    public function register()
    {
        $app = $this->app;

        $app->singleton('Lycee\Zend\CacheHelper', function ($app) {
            $cachePath = storage_path() . '/framework/cache/lycee-tcg.com';
            $storageParams = [
                'directory' => $cachePath,
            ];
            $storage = $app->make('Illuminate\Cache\FileStore', $storageParams);

            $cache = new \Illuminate\Cache\Repository($storage);

            $cacheHelper = new \Lycee\Zend\CacheHelper($cache);
        });

    }
}
