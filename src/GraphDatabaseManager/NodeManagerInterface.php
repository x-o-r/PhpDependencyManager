<?php

namespace PhpDependencyManager\GraphDatabaseManager;

interface NodeManagerInterface
{
    public function addNode($nodeFullNamespace, array $properties, array $label);
    public function addRelation($startNode, $relationType, $endNode);
    public function getNode($nodeFullNamespace);
}

