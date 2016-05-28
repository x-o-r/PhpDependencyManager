<?php

namespace PhpDependencyManager\GraphDatabaseManager;

use Everyman\Neo4j\Exception;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Node;

class Neo4JNodeManager extends NodeManagerAbstract
{
    /**
     * @param $nodeFullNamespace
     * @param array $properties
     * @param array $labels
     * @return Node
     * @throws GraphDatabaseManagerException
     */
    public function addNode($nodeFullNamespace, array $properties, array $labels)
    {
        try {
            if (empty($nodeFullNamespace)){
                throw new GraphDatabaseManagerException(__CLASS__ . ' : namespace cannot be empty');
            }
            $labelCollection = array();
            foreach($labels as $label){
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

    public function addRelation($startNode, $relationType, $endNode)
    {
        // TODO: Implement addRelation() method.
    }

    /**
     * @param $nodeFullNamespace
     * @return Node
     * @throws GraphDatabaseManagerException
     */
    public function getNode($nodeFullNamespace)
    {
        if(!array_key_exists($nodeFullNamespace,$this->nodeCollection)){
            Throw new GraphDatabaseManagerException(__CLASS__ . ' : no node found for key ' . $nodeFullNamespace);
        }
        return $this->nodeCollection[$nodeFullNamespace];
    }
}