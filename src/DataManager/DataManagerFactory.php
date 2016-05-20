<?php
namespace PhpDependencyManager\DataManager;
use Exception;
use Neoxygen\NeoClient\ClientBuilder;

class DataManagerFactory
{
    public static function getInstance()
    {
        $neo4jClient = null;
        try {
            $neo4jClient = ClientBuilder::create()
                ->addConnection('default', 'http', 'localhost', 7474, false)
                ->setAutoFormatResponse(true)
                ->build();

            $neo4jClient->ping();
        } catch (Exception $e)
        {
            Throw new DataManagerException($e);
        }
        return new DataManager($neo4jClient);
    }
}