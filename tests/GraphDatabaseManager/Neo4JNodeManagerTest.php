<?php

use PhpDependencyManager\GraphDatabaseManager\GraphDatabaseManagerException;
use PhpDependencyManager\GraphDatabaseManager\Neo4JFactory;
use PhpDependencyManager\GraphDatabaseManager\Neo4JNodeManager;
use Everyman\Neo4j\Relationship;

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
        $this->neo4JManager->deleteAllData();
        $this->fullNamespace = "A\\Name\\Space\\MyClass";
        $this->properties = array('name' => 'MyClass', 'fullnamespace' => $this->fullNamespace);
        $this->rawLabel = array('class');
        $this->node = $this->neo4JManager->addNode($this->fullNamespace, $this->properties, $this->rawLabel);
    }

    protected function tearDown()
    {
        if ($this->neo4JClient === null) {
            $this->fail('Neo4J connection failed');
        }
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

    public function testAddRelation()
    {
        $node1 = $this->neo4JManager->addNode('A\B', array('name' => 'Class1'), array('class', 'object'));
        $node2 = $this->neo4JManager->addNode('A\C', array('name' => 'Class2'), array('class', 'object'));

        $relation = $this->neo4JManager->addRelation($node1, $node2, 'LINK');
        $this->assertGreaterThan(0, count($node1->getRelationships(array('LINK'), Relationship::DirectionOut)));

        $this->neo4JClient->deleteRelationship($relation);
        $this->neo4JClient->deleteNode($node1);
        $this->neo4JClient->deleteNode($node2);
    }
}