<?php

namespace C2is\DoctrineCacheInvalidationBundle\Loader;

/**
 * Interface CacheConfigurationLoaderInterface
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
interface CacheConfigurationLoaderInterface
{
    /**
     * @param array $metadatas
     * @return array
     */
    public function loadCacheConfiguration(array $metadatas);
}
