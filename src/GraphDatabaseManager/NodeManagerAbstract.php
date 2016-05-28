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
    abstract public function addRelation($startNode, $relationType, $endNode);
    abstract public function getNode($nodeFullNamespace);
}