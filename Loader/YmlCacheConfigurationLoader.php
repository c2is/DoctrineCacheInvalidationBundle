<?php

namespace C2is\DoctrineCacheInvalidationBundle\Loader;

use Symfony\Component\Yaml\Yaml;

/**
 * Class YmlCacheConfigurationLoader
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
class YmlCacheConfigurationLoader implements CacheConfigurationLoaderInterface
{
    /** @var string */
    private $kernelRootDir;

    /** @var string */
    private $ymlFile;

    public function __construct($kernelRootDir, $ymlFile)
    {
        $this->kernelRootDir = $kernelRootDir;
        $this->ymlFile       = $ymlFile;
    }

    /**
     * {@inheritdoc}
     */
    public function loadCacheConfiguration(array $metadatas)
    {
        $cacheIds = Yaml::parse(file_get_contents($this->kernelRootDir . '/../' . $this->ymlFile));

        return $cacheIds;
    }
}
