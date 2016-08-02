<?php

namespace C2is\DoctrineCacheInvalidationBundle\CacheInvalidatorDriver;

use C2is\DoctrineCacheInvalidationBundle\Exception\InvalidArgumentException;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class AbstractCacheInvalidatorDriver
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
abstract class AbstractCacheInvalidatorDriver
{
    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var bool */
    protected $debug;

    public function setPropertyAccessor(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     * @param $cacheConfiguration
     * @param $entity
     * @return array
     */
    abstract public function getCacheIdsForEntity(OnFlushEventArgs $eventArgs, $cacheConfiguration, $entity);

    protected function parseCacheId(array $cacheId, $entity)
    {
        if (!array_key_exists('id', $cacheId)) {
            return false;
        }

        if (!array_key_exists('vars', $cacheId)) {
            return $cacheId['id'];
        }

        $parsedVars = array();

        foreach ($cacheId['vars'] as $var) {
            $resolvedVar = $this->resolveVar($var, $entity);
            if (null === $resolvedVar) {
                return false;
            }

            $parsedVars[] = $resolvedVar;
        }

        return vsprintf($cacheId['id'], $parsedVars);
    }

    protected function resolveVar($value, $entity)
    {
        if (false === $this->propertyAccessor->isReadable($entity, $value)) {
            $event = new GenericEvent($this, [
                'class' => get_class($entity),
                'value' => $value,
            ]);
            $this->eventDispatcher->dispatch('cache_invalidator.property_not_readable', $event);
            if (true === $this->debug) {
                throw new InvalidArgumentException(sprintf('Error : property "%s" is not readable on class %s', $value, get_class($entity)));
            }

            return null;
        }

        return $this->propertyAccessor->getValue($entity, $value);
    }
}
