<?php

namespace C2is\DoctrineCacheInvalidationBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Client as BaseClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Client
 */
class Client extends BaseClient
{
    protected static $connection;
    protected $requested;

    /**
     * @param Request $request A Request instance
     *
     * @return Response A Response instance
     */
    protected function doRequest($request)
    {
        if ($this->requested) {
            $this->getKernel()->shutdown();
            $this->getKernel()->boot();
        }

        if (null === self::$connection) {
            self::$connection = $this->getContainer()->get('doctrine.dbal.default_connection');
        } else {
            $this->getContainer()->set('doctrine.dbal.default_connection', self::$connection);
        }

        $this->requested = true;

        self::$connection->beginTransaction();

        $response = $this->getKernel()->handle($request);

        self::$connection->rollback();

        return $response;
    }
}