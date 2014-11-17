<?php
namespace Lycee\Zend;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CacheHelper
{

    protected $cache;
    private $defaultMinutes = 60 * 24 * 365 * 10;

    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    public function getCachedResult($method, $params = array(), $options = array())
    {
        $cache = $this->cache;
        $cacheKey = $this->getCacheKey($method, $params, $options);
        $ret = $cache->get($cacheKey);
        return $ret;
    }

    public function cacheResult($result, $method, $params = array(), $options = array())
    {
        $cache = $this->cache;
        $cacheKey = $this->getCacheKey($method, $params, $options);
        $ret = $cache[$cacheKey] = $result;

        return $ret;
    }

    public function getCacheKey($method, $params = array(), $options = array())
    {
        $cacheKey = !empty ($options['cache_key']) ?
            $options['cache_key'] :
            sha1($method . serialize($params) . serialize($options));
        return $cacheKey;
    }

    public function clearCachedResult($result, $params = array(), $options = array())
    {
        $cache = $this->cache;
        $cacheKey = $this->getCacheKey($result, $params, $options);
        $cache->forget($cacheKey);
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->cache, $method), $args);
    }
}

