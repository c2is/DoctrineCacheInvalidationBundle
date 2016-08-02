C2isDoctrineCacheInvalidationBundle
===================================

About
-----

This bundle provides an easy way to invalidate doctrine cache result.


Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require c2is/doctrine-cache-invalidation-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new new C2is\DoctrineCacheInvalidationBundle\C2isDoctrineCacheInvalidationBundle(),,
        );

        // ...
    }

    // ...
}
```

Usage
=====

Caching queries
---------------

The bundle invalidates doctrine's cache result unless you have activated it
and gave to your repositories' queries cacheIds.
 
Configure the doctrine cache in your `config_prod.yml` :
```yml
doctrine:
    orm:
        result_cache_driver: [cache_driver]
```

In any doctrine's repository, activate the cache for chosen queries :
```php
<?php
// src/AppBundle/Entity/MyEntityRepository.php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use C2is\DoctrineCacheInvalidationBundle\Annotation\CacheInvalidation;

class MyEntityRepository extends EntityRepository
{
    /**
     * @CacheInvalidation(cacheId="MyEntity:findAll")
     * @return mixed
     */
    public function findAll()
    {
        return $this->createQueryBuilder('e')
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true)
            ->setResultCacheId(md5('MyEntity:findAll'))
            ->getResult();
    }
}
```

That's it ! When Myentity entities will be inserted / updated / deleted,  `MyEntityRepository::findAll` cache result will be invalidated.
 
Queries with joins
------------------

You can specify which other entities' modification should invalidate the cache :
```php
<?php
// src/AppBundle/Entity/MyEntityRepository.php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use C2is\DoctrineCacheInvalidationBundle\Annotation\CacheInvalidation;

class MyEntityRepository extends EntityRepository
{
    /**
     * @CacheInvalidation(cacheId="MyEntity:findAll", entities={
     *  {"entity"="AppBundle\Entity\OtherEntity"},
     *  {"entity"="AppBundle\Entity\ThirdEntity"},
     * })
     * @return mixed
     */
    public function findAll()
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.otherEntity', 'o')
            ->leftJoin('e.thirdEntity', 't')
            ->addSelect('o, a')
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true)
            ->setResultCacheId(md5('MyEntity:findAll'))
            ->getResult();
    }
}
```

Invalidating cache for specific entity
--------------------------------------

Instead of invalidating all cache results related to one repository's method, there's a way to target only one cache id :
```php
<?php
// src/AppBundle/Entity/MyEntityRepository.php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use C2is\DoctrineCacheInvalidationBundle\Annotation\CacheInvalidation;

class MyEntityRepository extends EntityRepository
{
    /**
     * @CacheInvalidation(cacheId="MyEntity:findOneById:%s", vars={id}, entities={
     *  {"entity"="AppBundle\Entity\OtherEntity", "vars"={myEntity.id}},
     *  {"entity"="AppBundle\Entity\ThirdEntity", "vars"={myEntity.id}},
     * })
     * @return mixed
     */
    public function findOneById($id)
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.otherEntity', 'o')
            ->leftJoin('e.thirdEntity', 't')
            ->addSelect('o, a')
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true)
            ->setResultCacheId(md5(sprintf('MyEntity:findOneById:%s', $id)))
            ->getResult();
    }
}
```

As the bundles uses symfony's propertyAccessor, it's possible to chain properties. So, in this example : `"vars"={myEntity.id}` leads to `OtherEntity->getMyEntity()->getId()`

Be careful to `ManyToMany `and `OneToMany` relations : these shouldn't be declared with variables.


Compatibility with Gedmo\Translatable
-------------------------------------

A driver for [Gedmo\Translatable](https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/translatable.md) is provided in the bundle.
It is fully compatible with either `default translations`, `Translation Entity` or `Personal translations` methods.
Thanks to it, there's no need to declare translation's entities in the cache invalidators declaration.
  
Simply configure it in your `config.yml` :
```yml
c2is_doctrine_cache_invalidation:
    driver: gedmo
```

Configuration
=============

Cache invalidators declaration
------------------------------

Two possible cache invalidators type are supported : `yml` or `annotation` (default annotation) :

```yml
c2is_doctrine_cache_invalidation:
    type: json
    yml_file: [path_to.yml]
```

The yml path should be declared relatively to your application's root.
 
Here is exemple of the yml file structure, equivalent to the annotation example above :
```yml
AppBundle\Entity\MyEntity:
    -
        id: MyEntity:findOneById:%s
        vars:
            - id
    -
        id: MyEntity:findAll:%s
AppBundle\Entity\OtherEntity:
    -
        id: MyEntity:findOneById:%s
        vars:
            - myEntity.id
    -
        id: MyEntity:findAll:%s
AppBundle\Entity\ThirdEntity:
    -
        id: MyEntity:findOneById:%s
        vars:
            - myEntity.id
    -
        id: MyEntity:findAll:%s
```

Doctrine cache drivers
----------------------

For now, only `array` and `predis` are supported (default `array`)
Here is an example with `predis`

```yml
c2is_doctrine_cache_invalidation:
    doctrine_cache_driver_id: predis
    cache_driver_options:
        host: [redis_host]
        database: [redis_database_id]
```

Translation drivers
-------------------

Default configuration is no translation driver activated.
You can activate gedmo as shown above, or provide yours. It must extends `\C2is\DoctrineCacheInvalidationBundle\CacheInvalidatorDriver\AbstractCacheInvalidatorDriver`*

```yml
c2is_doctrine_cache_invalidation:
    driver: custom
    custom_driver_id: [custom_translation_driver_service_id]
```


TODO
====

As the bundle is young, there's a lot to do :
 - unit testing
 - set by default the doctrine's cache driver used in current environment
 - possibility to provide custom cache driver
 - change CacheInvalidatorDriver's name
 - provide parameters to invalidate cache only on insert / update or delete
