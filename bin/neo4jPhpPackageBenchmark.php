<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpDependencyManager\GraphDatabaseManager\Neo4JFactory;
use PhpDependencyManager\GraphDatabaseManager\Neo4JNodeManager;
use Everyman\Neo4j\Node;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Relationship;

if (empty($argv[1])) {
    die("Neo4j package must be specified [Everyman/Neo4j, Neoxygen/NeoClient, graphaware/neo4j-php-client]\n");
}

switch ($argv[1])
{
    case "Everyman/Neo4j" :

        $client = new Client('localhost', 7474);
        /*
        $node = $nodeManager->addNode("A\\Name\\Space\\MyClass", array('name' => 'MyClass', 'fullnamespace' => "A\\Name\\Space\\MyClass"), array('class'));
        $client->deleteNode($node);
        */

        $transaction = $client->beginTransaction();
        $query = new Query($client, 'MATCH (n) DETACH DELETE n');
        $transaction->addStatements($query);
        $transaction->commit();


        $transaction = $client->beginTransaction();
        $classLabel = $client->makeLabel("class");

        $node1 = $client->makeNode()->setProperty('name', "n1")->save();
        $node1->addLabels(array($classLabel));
        $node2 = $client->makeNode()->setProperty('name', "n2")->save();
        $node2->addLabels(array($classLabel));
        $transaction->commit();

        $transaction = $client->beginTransaction();
        $relation = $client->makeRelationship();
        $relation->setStartNode($node1)->setEndNode($node2)->setType('LINK')->save();
        $transaction->commit();

        $node1Relationships = $node1->getRelationships(array('LINK'), Relationship::DirectionOut);
        var_dump($node1Relationships);

        /*
        $transaction = $client->beginTransaction();
        $classLabel = $client->makeLabel("class");

        for($i=0; $i < 10000; $i++){
           try{
               if ($i % 100 === 0) { // @TODO add const for max commit number
                   $transaction->commit();
                   $transaction = $client->beginTransaction();
               }
               $currentNode = $client->makeNode();
               $currentNode->setProperty('name', "a\\node" . $i)->save();
           } catch (Exception $e){
               $transaction->rollback();
               Throw new Exception($e);
           }
        }
        $transaction->commit();
       */

        break;
    case "Neoxygen/NeoClient" :
        $neo4jClient = Neoxygen\NeoClient\ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, false)
            ->setAutoFormatResponse(true)
            ->build();

        $query = null;
        for($i=0; $i < 10000; $i++){
            $query.= "CREATE (`node" . $i . ":class` {name:'`a\\\\node" . $i . "`'})";
        }
        $neo4jClient->sendCypherQuery($query);
        break;
    case "Neoxygen/graphaware/neo4j-php-client" :
        $client = GraphAware\Neo4j\Client\ClientBuilder::create()
            ->addConnection('default', 'http://localhost:7474')
            ->build();

        $stack = $client->stack();
        for($i=0; $i < 10000; $i++){
            if ($i % 100 === 0) {
                $client->runStack($stack);
                $stack = $client->stack();
            }
            $stack->push("CREATE (`node" . $i . ":class` {name:'`a\\node" . $i . "`'})");
        }
        $client->runStack($stack);

        break;
    default :
        break;
}
