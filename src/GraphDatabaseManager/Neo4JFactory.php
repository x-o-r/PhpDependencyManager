<?php

namespace PhpDependencyManager\GraphDatabaseManager;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Exception;

class Neo4JFactory
{
    /**
     * @param array $connectionProperties
     * @return Client|null
     * @throws GraphDatabaseManagerException
     */
    public static function getNeo4JClient(array $connectionProperties)  {
        if (
            !array_key_exists('host', $connectionProperties) ||
            !array_key_exists('port', $connectionProperties) ||
            empty($connectionProperties['host']) ||
            empty($connectionProperties['port'])
        ){
            throw new GraphDatabaseManagerException(__CLASS__ . " : Keys 'host' and 'port' must be defined");
        }
        $neo4Jclient = null;
        try {
            $neo4Jclient = new Client($connectionProperties['host'], $connectionProperties['port']);
            if(!is_null($neo4Jclient->getServerInfo())) {
                return $neo4Jclient;
            }
        } catch (Exception $e){
            throw new GraphDatabaseManagerException(__CLASS__ . ' : connection failed');
        }
    }
}