<?php

namespace PhpDependencyManager\GraphDatabaseManager;

interface NodeManagerInterface
{
    public function addNode($nodeFullNamespace, array $properties, array $label);
    public function addRelation($startNode, $endNode, $relationType, array $relationProperties = null);
    public function getNode($nodeFullNamespace);
}

