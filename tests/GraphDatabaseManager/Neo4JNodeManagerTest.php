<?php

use PhpDependencyManager\GraphDatabaseManager\GraphDatabaseManagerException;
use PhpDependencyManager\GraphDatabaseManager\Neo4JFactory;
use PhpDependencyManager\GraphDatabaseManager\Neo4JNodeManager;

class Neo4JNodeManagerTest extends PHPUnit_Framework_TestCase
{
    private $neo4JClient;
    private $neo4JManager;
    private $fullNamespace;
    private $properties;
    private $rawLabel;
    private $node;

    protected function setUp()
    {
        $this->neo4JClient = Neo4JFactory::getNeo4JClient(array('host'=>'localhost', 'port'=>'7474'));
        $this->neo4JManager = new Neo4JNodeManager($this->neo4JClient);
        $this->fullNamespace = "A\\Name\\Space\\MyClass";
        $this->properties = array('name' => 'MyClass', 'fullnamespace' => $this->fullNamespace);
        $this->rawLabel = array('class');
        $this->node = $this->neo4JManager->addNode($this->fullNamespace, $this->properties, $this->rawLabel);
    }

    protected function tearDown()
    {
        $this->neo4JClient->deleteNode($this->node);
        $this->node = null;
    }

    public function testAddNode()
    {

        try {
            $this->neo4JManager->addNode($this->fullNamespace, $this->properties, $this->rawLabel);
        } catch (GraphDatabaseManagerException $exception) {
            $this->fail();
        }

        $this->assertTrue(TRUE);
    }

    public function testAddNodeWithNoLabel()
    {
            $this->expectException(Exception::class);
            $this->neo4JManager->addNode($this->fullNamespace, $this->properties, array());
    }

    public function testGetNode()
    {
        $this->neo4JManager->addNode($this->fullNamespace, $this->properties, $this->rawLabel);
        $node = $this->neo4JManager->getNode($this->fullNamespace);
        $this->assertInstanceOf('Everyman\Neo4j\Node', $node);
    }

    public function testGetNonExistantNode()
    {
        $this->expectException(GraphDatabaseManagerException::class);

        $this->assertNull($this->neo4JManager->getNode(''));
    }
}