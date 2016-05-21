<?php

namespace PhpDependencyManager\DataManager;

use PhpDependencyManager\DTO\ClassDTO;
use PhpDependencyManager\DTO\InterfaceDTO;
use PhpDependencyManager\StringFilter;

class DataManager
{
    private $client = null;
    private $rootNamespaceCollection = array(); // Key : 'root namespace', value : 'component name'
    private $fullComponentCollection = array();

    private $fullComponentQueryCollection = array();
    private $realComponentQueryCollection = array();
    private $nodeQueryCollection = array();
    private $relationQueryCollection = array();

    public function __construct($neo4jClient)
    {
        $this->client = $neo4jClient;
        $this->query = array();
    }

    public function getQuery() {
        return $this->query;
    }

    public function dropSchema() {
        $this->client->sendCypherQuery("MATCH (n) detach delete n");
    }

    public function createNode($nodeName, $nodeType, array $attributes = null) {
        $query = "CREATE (" . $nodeName . ":" . $nodeType;
        if (!is_null($attributes) && is_array($attributes)){
            $query .= json_encode($attributes);
        }
        $query .= ")";

        if ($nodeType === "class" || $nodeType === "interface"){
            array_push($this->nodeQueryCollection, $query);
        }
        if ($nodeType === "component" || $nodeType === "interface"){
            array_push($this->fullComponentQueryCollection, $query);
        }
    }

    public function createRelation($fromNode, $relationName, $toNode) {
        array_push($this->relationQueryCollection, "CREATE (" . $fromNode . ")-[:" . $relationName . "{type:'" . $relationName . "'}]->(" . $toNode . ")");
    }

    private function getDTONameFromKey($key) {
        $className = explode(':', $key);
        return array_shift($className);
    }

    public function createNamespace($namespace){
        if (!empty($namespace)){
            $namespaceParts = null;
            $previousPart = null;

            if (is_array($namespace)){
                $namespaceParts = $namespace;
            } else {
                $namespaceParts = explode('_', $namespace);
            }

            foreach($namespaceParts as $part)
            {
                // Create node
                $this->createNode($part, "namespace", array('full_namespace' => $namespace));
                if (!is_null($previousPart)) {// If has previous node create relation and save previous node
                    $this->createRelation($previousPart, "ISIN", $part);
                    $previousPart = $part;
                } else // Create component root namespace relation
                {
                    array_push($this->rootNamespaceCollection, $part);
                }
            }
        }
    }

    public function createSchema(array $objectDTOCollection, array $componentDTOCollection = null) {

        $classNameToID = array();
        // First pass on $objectDTOCollection to build a hastable with key:'non unique class name' value:'unique class name'
        foreach (array_keys($objectDTOCollection) as $objectKey) {
            $classNameToID[$this->getDTONameFromKey($objectKey)][$objectKey]=$objectKey;
        }

        // Componnent
        foreach ($componentDTOCollection as $component) {
            $this->fullComponentCollection[$component->getName()] = $component;

            if (!empty($component->getNamespaces())){
                foreach($component->getNamespaces() as $rootNamespace){
                    $this->rootNamespaceCollection[$rootNamespace] = $component->getName();
                }
            }
        }

        // Classes and interfaces
        foreach (array_keys($objectDTOCollection) as $objectKey) {

            $obj = $objectDTOCollection[$objectKey];

            $namespace = $obj->getNamespace();
            if (empty($namespace)){
                $namespace = "no_namespace";
            } else{
                $this->createNamespace($namespace);
            }

            if (!empty($obj->getExtend())) { // Aggregate
            }

            if ($obj instanceof ClassDTO) {
                $this->createNode($obj->getName(), "class", array('namespace' => $namespace));
                $classInstances = $obj->getClassesInstances();
                if (!empty($classInstances) && count($classInstances)) { // Compose
                    foreach ($classInstances as $objInstance) {
                        if (key_exists($objInstance, $classNameToID) && key_exists($objectKey, $classNameToID[$objInstance])) {
                            $objInstanceFromKnownObject = $classNameToID[$objInstance][$objectKey];
                            $objInstanceFromKnownObjectNamespace = $objInstanceFromKnownObject->getNamespace();
                            if (empty($objInstanceFromKnownObjectNamespace)) {
                             $objInstanceFromKnownObjectNamespace = "no_namespace";
                            }
                            $this->createNode($objInstanceFromKnownObject->getName(), "class", array('namespace' => $objInstanceFromKnownObjectNamespace));
                            $this->createRelation($obj->getName(), "COMPOSE", $objInstanceFromKnownObject->getName());
                        } else {
                            $this->createNode($objInstance, "undiscovered_class", array('namespace' => 'undiscovered_namespace'));
                            $this->createRelation($obj->getName(), "COMPOSE", $objInstance);
                        }
                    }
                }
            }
            if ($obj instanceof InterfaceDTO) {
            }
        }

        $this->rootNamespaceCollection = array_unique($this->rootNamespaceCollection);
        $this->fullComponentQueryCollection = array_unique($this->fullComponentQueryCollection);
        $this->realComponentQueryCollection = array_unique($this->realComponentQueryCollection);
        $this->nodeQueryCollection = array_unique($this->nodeQueryCollection);
        $this->relationQueryCollection = array_unique($this->relationQueryCollection);

        // Namespace > Component relations
        foreach($this->rootNamespaceCollection as $rootNamespace => $componentName){
            if (array_key_exists($componentName, $this->fullComponentCollection)){
                $this->createNode($componentName, "component");
                $this->createRelation($rootNamespace, "DECLAREDIN", $componentName);
            }
        }
        var_dump( $this->nodeQueryCollection);
        var_dump( $this->fullComponentQueryCollection);
        var_dump( $this->relationQueryCollection);
        exit;

        $objects    = implode (' ', $this->nodeQueryCollection);
        $components = implode (' ', $this->fullComponentQueryCollection);
        $relations  = implode (' ', $this->relationQueryCollection);

        $query = $objects.$components.$relations;

        if(!is_null($this->client)){
            $this->client->sendCypherQuery($query);
        }
    }
}


