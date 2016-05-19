<?php
namespace PhpDependencyManager;
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
        } catch (Error $e)
        {
            return null;
        }
        return new DataManager($neo4jClient);
    }
}