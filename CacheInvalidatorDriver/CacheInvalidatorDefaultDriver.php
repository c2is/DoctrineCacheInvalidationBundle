<?php

namespace C2is\DoctrineCacheInvalidationBundle\CacheInvalidatorDriver;

use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * Class CacheInvalidatorDefaultDriver
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
class CacheInvalidatorDefaultDriver extends AbstractCacheInvalidatorDriver
{
    /**
     * {@inheritdoc}
     */
    public function getCacheIdsForEntity(OnFlushEventArgs $eventArgs, $cacheConfiguration, $entity)
    {
        $className = get_class($entity);

        if (!array_key_exists($className, $cacheConfiguration)) {
            return array();
        }

        $cacheToInvalidate = array();

        foreach ($cacheConfiguration[$className] as $cacheId) {
            if ($parsedId = $this->parseCacheId($cacheId, $entity)) {
                $cacheToInvalidate[] = $parsedId;
            }
            unset($parsedId);
        }

        return $cacheToInvalidate;
    }
}
