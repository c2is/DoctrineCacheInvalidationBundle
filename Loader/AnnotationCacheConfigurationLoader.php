<?php

namespace C2is\DoctrineCacheInvalidationBundle\Loader;

use C2is\DoctrineCacheInvalidationBundle\Exception\InvalidArgumentException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ClassMetadata;
use C2is\DoctrineCacheInvalidationBundle\Annotation\CacheInvalidation;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class AnnotationCacheConfigurationLoader
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
class AnnotationCacheConfigurationLoader implements CacheConfigurationLoaderInterface
{
    /** @var AnnotationReader */
    private $annotationReader;

    public function __construct(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadCacheConfiguration(array $metadatas)
    {
        $cacheIds  = [];

        /** @var ClassMetadata $metadata */
        foreach ($metadatas as $metadata) {
            if (null !== $metadata->customRepositoryClassName) {
                $reflClass = new ReflectionClass($metadata->customRepositoryClassName);

                /** @var ReflectionMethod $method */
                foreach ($reflClass->getMethods() as $reflMethod) {
                    /** @var CacheInvalidation $cacheInvalidationAnnotation */
                    $cacheInvalidationAnnotation = $this->annotationReader->getMethodAnnotation($reflMethod, 'C2is\DoctrineCacheInvalidationBundle\Annotation\CacheInvalidation');

                    if (null !== $cacheInvalidationAnnotation) {
                        $this->validateCacheInvalidationAnnotation($cacheInvalidationAnnotation, $metadata->customRepositoryClassName, $reflMethod->getName());

                        if (!isset($cacheIds[$metadata->name])) {
                            $cacheIds[$metadata->name] = [];
                        }

                        $cacheResultConfiguration = [
                            'id'   => $cacheInvalidationAnnotation->getCacheId(),
                            'vars' => []
                        ];
                        if (count($cacheInvalidationAnnotation->getVars()) > 0) {
                            foreach ($cacheInvalidationAnnotation->getVars() as $var) {
                                $cacheResultConfiguration['vars'][] = $var;
                            }
                        } else {
                            unset($cacheResultConfiguration['vars']);
                        }
                        $cacheIds[$metadata->name][] = $cacheResultConfiguration;

                        if (count($cacheInvalidationAnnotation->getEntities()) > 0) {
                            foreach ($cacheInvalidationAnnotation->getEntities() as $entity) {
                                if (!isset($cacheIds[$entity['entity']])) {
                                    $cacheIds[$entity['entity']] = [];
                                }

                                $cacheResultConfiguration = [
                                    'id'   => $cacheInvalidationAnnotation->getCacheId(),
                                    'vars' => []
                                ];
                                if (isset($entity['vars']) && count($entity['vars']) > 0) {
                                    foreach ($entity['vars'] as $var) {
                                        $cacheResultConfiguration['vars'][] = $var;
                                    }
                                } else {
                                    unset($cacheResultConfiguration['vars']);
                                }

                                $cacheIds[$entity['entity']][] = $cacheResultConfiguration;
                            }
                        }
                    }
                }
            }
        }

        return $cacheIds;
    }

    /**
     * @param CacheInvalidation $annotation
     * @param string $repositoryName
     * @param string $method
     * @throws \InvalidArgumentException
     */
    private function validateCacheInvalidationAnnotation(CacheInvalidation $annotation, $repositoryName, $method)
    {
        if (null === $annotation->getCacheId()) {
            throw new InvalidArgumentException(sprintf(
                'CacheInvalidationError : CacheId is mandatory. Annotation defined on %s::%s',
                $repositoryName,
                $method
            ));
        }

        if (null !== $annotation->getEntities()) {
            if (!is_array($annotation->getEntities())) {
                throw new InvalidArgumentException(sprintf(
                    'CacheInvalidationError : entities must be an array. Annotation defined on %s::%s',
                    $repositoryName,
                    $method
                ));
            }

            foreach ($annotation->getEntities() as $annotationEntity) {
                if (!is_array($annotationEntity) || !array_key_exists('entity', $annotationEntity)) {
                    throw new InvalidArgumentException(sprintf(
                        'CacheInvalidationError : Malformed entities array. Annotation defined on %s::%s',
                        $repositoryName,
                        $method
                    ));
                }
                if (!class_exists($annotationEntity['entity'])) {
                    throw new InvalidArgumentException(sprintf(
                        'CacheInvalidationError : Class %s does not exist. Annotation defined on %s::%s',
                        $annotationEntity['entity'],
                        $repositoryName,
                        $method
                    ));
                }
            }
        }
    }
}
