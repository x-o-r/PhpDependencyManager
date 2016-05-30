<?php

namespace PhpDependencyManager\GraphDatabaseManager;

use Everyman\Neo4j\Exception;
use Everyman\Neo4j\Node;
use Everyman\Neo4j\Cypher\Query;

class Neo4JNodeManager extends NodeManagerAbstract
{
    /**
     * @param $nodeID
     * @param array $properties
     * @param array $labels
     * @return Node
     * @throws GraphDatabaseManagerException
     */
    public function addNode($nodeID, array $properties, array $labels) {
        try {
            if (empty($nodeID)) {
                throw new GraphDatabaseManagerException(__CLASS__ . ' : node id cannot be empty');
            }

//            if (array_key_exists($nodeID, $this->nodeCollection)) {
//                throw new GraphDatabaseManagerException(__CLASS__ . ' : node id ' . $nodeID . 'already exists');
//            }

            $labelCollection = array();

            foreach($labels as $label) {
                array_push($labelCollection, $this->graphDatabaseHandler->makeLabel($label));
            }
            $this->graphDatabaseHandler->makeLabel('class');
            $node = $this->graphDatabaseHandler->makeNode()->setProperties($properties)->save();
            $node->addLabels($labelCollection);
            $this->nodeCollection[$nodeID] = $node;
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
     * @param $nodeID
     * @return Node
     * @throws GraphDatabaseManagerException
     */
    public function getNode($nodeID) {
        if(!array_key_exists($nodeID,$this->nodeCollection)){
            Throw new GraphDatabaseManagerException(__CLASS__ . ' : no node found for key ' . $nodeID);
        }
        return $this->nodeCollection[$nodeID];
    }

    public function deleteAllData() {
        $transaction = $this->graphDatabaseHandler->beginTransaction();
        $query = new Query($this->graphDatabaseHandler, 'MATCH (n) DETACH DELETE n');
        $transaction->addStatements($query);
        $transaction->commit();
    }
}