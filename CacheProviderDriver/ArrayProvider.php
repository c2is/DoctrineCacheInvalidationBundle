<?php

namespace C2is\DoctrineCacheInvalidationBundle\CacheProviderDriver;

use Doctrine\Common\Cache\ArrayCache;

/**
 * Class ArrayProvider
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
class ArrayProvider implements CacheProviderDriverInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCacheProvider(array $options = [])
    {
        return new ArrayCache();
    }
}
