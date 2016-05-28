<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpDependencyManager\GraphDatabaseManager\Neo4JFactory;
use PhpDependencyManager\GraphDatabaseManager\Neo4JNodeManager;

if (empty($argv[1])) {
    die("Neo4j package must be specified [Everyman/Neo4j, Neoxygen/NeoClient, graphaware/neo4j-php-client]\n");
}

switch ($argv[1])
{
    case "Everyman/Neo4j" :

        $client = Neo4JFactory::getNeo4JClient(array('host'=>'localhost', 'port'=>'7474'));
        $nodeManager = new Neo4JNodeManager($client);

        $node = $nodeManager->addNode("A\\Name\\Space\\MyClass", array('name' => 'MyClass', 'fullnamespace' => "A\\Name\\Space\\MyClass"), array('class'));
        $client->deleteNode($node);

        /*
       $transaction = $client->beginTransaction();
       $query = new Everyman\Neo4j\Cypher\Query($client, 'MATCH (n) DETACH DELETE n');
       $transaction->addStatements($query);
       $transaction->commit();

       $classLabel = $client->makeLabel("class");

       $node1 = $client->makeNode()->setProperty('name', "n1")->save();
       $node2 = $client->makeNode()->setProperty('name', "n2")->save();

       $client->getNode($node1->getId())->addLabels(array($classLabel));
       $client->getNode($node2->getId())->addLabels(array($classLabel));
       $relation = $client->makeRelationship();
       $relation->setStartNode($node1)->setEndNode($node2)->setType('LINK')->save();


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
