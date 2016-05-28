<?php

namespace PhpDependencyManager\GraphDatabaseManager;

use Everyman\Neo4j\Exception;
use Everyman\Neo4j\Node;
use Everyman\Neo4j\Cypher\Query;

class Neo4JNodeManager extends NodeManagerAbstract
{
    /**
     * @param $nodeFullNamespace
     * @param array $properties
     * @param array $labels
     * @return Node
     * @throws GraphDatabaseManagerException
     */
    public function addNode($nodeFullNamespace, array $properties, array $labels) {
        try {
            if (empty($nodeFullNamespace)) {
                throw new GraphDatabaseManagerException(__CLASS__ . ' : namespace cannot be empty');
            }
            $labelCollection = array();
            foreach($labels as $label) {
                array_push($labelCollection, $this->graphDatabaseHandler->makeLabel($label));
            }
            $this->graphDatabaseHandler->makeLabel('class');
            $node = $this->graphDatabaseHandler->makeNode()->setProperties($properties)->save();
            $node->addLabels($labelCollection);
            $this->nodeCollection[$nodeFullNamespace] = $node;
            return $node;
        } catch (Exception $e) {
            throw new GraphDatabaseManagerException(__CLASS__ . ' : ' . $e);
        }
    }

    /**
     * @param $startNode
     * @param $endNode
     * @param $relationType
     * @param array|null $relationProperties
     */
    public function addRelation($startNode, $endNode, $relationType, array $relationProperties = null) {
        if($relationProperties === null) {
            $relationProperties = array();
        }
        $relation = $this->graphDatabaseHandler->makeRelationship();
        $relation->setStartNode($startNode)->setEndNode($endNode)->setType($relationType)->setProperties($relationProperties)->save();
        return $relation;
    }

    /**
     * @param $nodeFullNamespace
     * @return Node
     * @throws GraphDatabaseManagerException
     */
    public function getNode($nodeFullNamespace) {
        if(!array_key_exists($nodeFullNamespace,$this->nodeCollection)){
            Throw new GraphDatabaseManagerException(__CLASS__ . ' : no node found for key ' . $nodeFullNamespace);
        }
        return $this->nodeCollection[$nodeFullNamespace];
    }

    public function deleteAllData() {
        $transaction = $this->graphDatabaseHandler->beginTransaction();
        $query = new Query($this->graphDatabaseHandler, 'MATCH (n) DETACH DELETE n');
        $transaction->addStatements($query);
        $transaction->commit();
    }
}