<?php

namespace C2is\DoctrineCacheInvalidationBundle\CacheProviderDriver;

use Doctrine\Common\Cache\PredisCache;
use Predis\Client;

/**
 * Class PredisProvider
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
class PredisProvider implements CacheProviderDriverInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCacheProvider(array $options = [])
    {
        return new PredisCache(new Client([
            'host' => $options['host'],
            'port' => $options['port'],
            'database' => $options['database'],
        ]));
    }
}
