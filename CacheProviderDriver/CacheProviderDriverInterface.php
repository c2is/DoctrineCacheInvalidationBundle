<?php

namespace C2is\DoctrineCacheInvalidationBundle\CacheProviderDriver;

use Doctrine\Common\Cache\CacheProvider;

/**
 * Interface CacheProviderDriverInterface
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
interface CacheProviderDriverInterface
{
    /**
     * @param array $options
     * @return CacheProvider
     */
    public function getCacheProvider(array $options = []);
}
