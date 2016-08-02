<?php

namespace C2is\DoctrineCacheInvalidationBundle\CacheInvalidatorDriver;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * Class CacheInvalidatorGedmoDriver
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
class CacheInvalidatorGedmoDriver extends AbstractCacheInvalidatorDriver
{
    /**
     * {@inheritdoc}
     */
    public function getCacheIdsForEntity(OnFlushEventArgs $eventArgs, $cacheConfiguration, $entity)
    {
        $entityManager = $eventArgs->getEntityManager();
        $className     = get_class($entity);
        if ($entity instanceof AbstractTranslation) {
            $translationEntity = $entity;
            $className         = $translationEntity->getObjectClass();

            /** @var EntityRepository $repository */
            $repository      = $entityManager->getRepository($className);
            $cacheableEntity = $repository->findOneBy(['id' => $translationEntity->getForeignKey()]);
            $cacheableEntity->setLocale($translationEntity->getLocale());
            $entityManager->refresh($cacheableEntity);
        } elseif ($entity instanceof AbstractPersonalTranslation) {
            $className       = get_class($entity->getObject());
            $cacheableEntity = $entity->getObject();
        } else {
            $cacheableEntity = $entity;
        }

        if (!array_key_exists($className, $cacheConfiguration)) {
            return array();
        }

        $cacheToInvalidate = array();

        foreach ($cacheConfiguration[$className] as $cacheId) {
            if ($parsedId = $this->parseCacheId($cacheId, $cacheableEntity)) {
                $cacheToInvalidate[] = $parsedId;
            }
            unset($parsedId);
        }

        return $cacheToInvalidate;
    }
}
