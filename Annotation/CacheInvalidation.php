<?php

namespace C2is\DoctrineCacheInvalidationBundle\Annotation;

use C2is\DoctrineCacheInvalidationBundle\Exception\InvalidArgumentException;

/**
 * @Annotation
 * @Target("METHOD")
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
class CacheInvalidation
{
    /** @var string */
    private $cacheId;

    /** @var array */
    private $entities;

    /** @var array */
    private $vars;

    /**
     * @param array $options
     * @throws \Exception
     */
    public function __construct($options)
    {
        if (isset($options['cacheId'])) {
            $this->cacheId = $options['cacheId'];
        } else {
            throw new InvalidArgumentException('CacheInvalidationAnnotation : cacheId is mandatory');
        }

        if (isset($options['entities'])) {
            if (is_array($options['entities'])) {
                $this->entities = $options['entities'];
            } else {
                throw new InvalidArgumentException('CacheInvalidationAnnotation : entities parameter must be an array');
            }
        } else {
            $this->entities = [];
        }

        if (isset($options['vars'])) {
            $this->vars = $options['vars'];
        } else {
            $this->vars = [];
        }
    }

    public function getCacheId()
    {
        return $this->cacheId;
    }

    public function getEntities()
    {
        return $this->entities;
    }

    public function getVars()
    {
        return $this->vars;
    }
}
