<?php

use PhpDependencyManager\GraphDatabaseManager\GraphDatabaseManagerException;
use PhpDependencyManager\GraphDatabaseManager\Neo4JFactory;

class Neo4JFactoryTest extends PHPUnit_Framework_TestCase
{
    private $neo4JClient;

    protected function tearDown()
    {
        $this->neo4JClient = null;
    }

    public function testMissingConnectionProperties()
    {
        $this->expectException(GraphDatabaseManagerException::class);
        $this->neo4JClient = Neo4JFactory::getNeo4JClient(array());
    }

    public function testBadConnectionPropertiesValues()
    {
        $this->expectException(GraphDatabaseManagerException::class);
        $this->neo4JClient = Neo4JFactory::getNeo4JClient(array('host'=>'', 'port'=>''));
    }

    public function testConnection()
    {
        $this->neo4JClient = Neo4JFactory::getNeo4JClient(array('host'=>'localhost', 'port'=>'7474'));
        $this->assertInstanceOf('Everyman\Neo4j\Client', $this->neo4JClient);
    }
}