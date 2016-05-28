<?php

namespace PhpDependencyManager\GraphDatabaseManager;

abstract class NodeManagerAbstract implements NodeManagerInterface
{
    protected $nodeCollection = array(); // Key : nodeFullNamespace, value : Node
    protected $graphDatabaseHandler;

    public function __construct($graphDatabaseHandler) {
        $this->graphDatabaseHandler = $graphDatabaseHandler;
    }
    abstract public function addNode($nodeFullNamespace, array $properties, array $label);
    abstract public function addRelation($startNode, $endNode, $relationType, array $relationProperties = null);
    abstract public function getNode($nodeFullNamespace);

    public function getNodeCollectionKeys() {
        return array_keys($this->nodeCollection);
    }
}