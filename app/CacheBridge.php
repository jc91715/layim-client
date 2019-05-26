<?php

namespace App;

use Doctrine\Common\Cache\Cache as CacheInterface;
use \Cache;

class CacheBridge implements CacheInterface
{
    public function fetch($id)
    {
        return Cache::get($id);
    }

    public function contains($id)
    {
        return Cache::has($id);
    }

    public function save($id, $data, $lifeTime = 0)
    {
        if ($lifeTime == 0) {
            return Cache::forever($id, $data);
        } else {
            return Cache::put($id, $data, $lifeTime / 60);
        }
    }

    public function delete($id)
    {
        return Cache::forget($id);
    }

    public function flush()
    {
        return Cache::flush();
    }

    public function getStats()
    {
        return [];
    }
}