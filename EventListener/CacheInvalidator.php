<?php

namespace C2is\DoctrineCacheInvalidationBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use C2is\DoctrineCacheInvalidationBundle\CacheInvalidatorDriver\AbstractCacheInvalidatorDriver;
use C2is\DoctrineCacheInvalidationBundle\CacheProviderDriver\CacheProviderDriverInterface;
use C2is\DoctrineCacheInvalidationBundle\Loader\CacheConfigurationLoaderInterface;

/**
 * Class CacheInvalidator
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
class CacheInvalidator
{
    protected $cacheConfiguration;

    /** @var CacheProviderDriverInterface */
    private $cacheProviderDriver;

    /** @var CacheConfigurationLoaderInterface */
    private $cacheIdsLoader;

    /** @var AbstractCacheInvalidatorDriver */
    private $cacheInvalidatorDriver;

    /** @var array  */
    private $cacheDriverOptions;

    public function __construct(
        CacheConfigurationLoaderInterface $cacheIdsLoader,
        AbstractCacheInvalidatorDriver $cacheInvalidatorDriver,
        CacheProviderDriverInterface $cacheProviderDriver,
        array $cacheDriverOptions
    ) {
        $this->cacheIdsLoader         = $cacheIdsLoader;
        $this->cacheInvalidatorDriver = $cacheInvalidatorDriver;
        $this->cacheProviderDriver    = $cacheProviderDriver;
        $this->cacheDriverOptions     = $cacheDriverOptions;
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork    = $entityManager->getUnitOfWork();

        if (null === $this->cacheConfiguration) {
            $this->cacheConfiguration = $this->cacheIdsLoader->loadCacheConfiguration($eventArgs->getEntityManager()->getMetadataFactory()->getAllMetadata());
        }

        $scheduledEntityChanges = array(
            'insert' => $unitOfWork->getScheduledEntityInsertions(),
            'update' => $unitOfWork->getScheduledEntityUpdates(),
            'delete' => $unitOfWork->getScheduledEntityDeletions()
        );

        $cacheIds = array();
        foreach ($scheduledEntityChanges as $entities) {
            foreach ($entities as $entity) {
                $cacheIds = array_merge($cacheIds, $this->cacheInvalidatorDriver->getCacheIdsForEntity(
                    $entityManager,
                    $this->cacheConfiguration,
                    $entity
                ));
            }
        }

        if (count($cacheIds) == 0) {
            return;
        }

        $cacheIds = array_unique($cacheIds);

        $entityManager->getConfiguration()->setResultCacheImpl($this->cacheProviderDriver->getCacheProvider($this->cacheDriverOptions));
        $resultCache = $entityManager->getConfiguration()->getResultCacheImpl();

        foreach ($cacheIds as $cacheId) {
            $resultCache->delete(md5($cacheId));
        }
    }
}
