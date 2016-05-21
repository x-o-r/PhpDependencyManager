<?php

namespace PhpDependencyManager\DataManager;

use PhpDependencyManager\DTO\ClassDTO;
use PhpDependencyManager\DTO\InterfaceDTO;
use PhpDependencyManager\StringFilter;

class DataManager
{
    private $client = null;
    private $query = null;
    private $rootNamespaceCollection = array(); // Key : 'root namespace', value : 'component name'
    private $componentNamespaceCollection = array();
    private $fullComponentCollection = array();

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

    public function sendQuery(){
        if(!is_null($this->client)){
            $this->client->sendCypherQuery($this->query);
        }
    }

    public function dropSchema() {
        $this->client->sendCypherQuery("MATCH (n) detach delete n");
    }

    public function createNode($nodeName, $nodeType, $attributes = null) {
        $query = "CREATE (" . $nodeName . ":" . $nodeType . "{name:'" . $nodeName . "',";
        if (is_array($attributes) && count($attributes)){
            foreach ($attributes as $key => $value){
                $query .= str_replace('"', '', $key) . ":'" . str_replace('"', '', $value) . "',";
            }
        }
        $query = substr($query, 0, -1);
        $query .= '})';

        if ($nodeType === "class" || $nodeType === "interface" || $nodeType === "namespace"){
            array_push($this->nodeQueryCollection, $query);
        }
        if ($nodeType === "component"){
            array_push($this->realComponentQueryCollection, $query);
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
            $namespaceParts = explode('_', $namespace);

            foreach($namespaceParts as $part)
            {
                // Create node
                $this->createNode($part, "namespace");//, array("full_namespace" => $namespace));
                if (is_null($previousPart)) { // Create component root namespace relation
                    array_push($this->rootNamespaceCollection, $part);
                    $previousPart = $part;
                } else { // If previous namespace node exists, create relation and save previous node
                    $this->createRelation($part, "IS_IN", $previousPart);
                    $previousPart = $part;
                }
            }
        }
    }

    public function createSchema(array $objectDTOCollection, array $componentDTOCollection = null) {

        $classNameToID = array();
        // First pass on $objectDTOCollection to build a hastable with key:'non unique class name' value:'unique class         '
        foreach (array_keys($objectDTOCollection) as $objectKey) {
            $classNameToID[$this->getDTONameFromKey($objectKey)][$objectKey]=$objectDTOCollection[$objectKey];
        }

        // Componnent
        foreach ($componentDTOCollection as $component) {
            $this->fullComponentCollection[$component->getName()] = $component;

            if (!empty($component->getNamespaces())){
                foreach($component->getNamespaces() as $rootNamespace){
                    $this->componentNamespaceCollection[$rootNamespace] = $component->getName();
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

            if (!empty($obj->getExtend())) { // Extend

            }

            if ($obj instanceof ClassDTO) {
                $this->createNode($obj->getName(), "class", array("namespace" => $namespace));
                $classInstances = $obj->getClassesInstances();
                if (!empty($classInstances) && count($classInstances)) { // Compose
                    foreach ($classInstances as $objInstance) {
                        if (key_exists($objInstance, $classNameToID) && key_exists($objectKey, $classNameToID[$objInstance])) {
                            $objInstanceFromKnownObject = $classNameToID[$objInstance][$objectKey];
                            $objInstanceFromKnownObjectNamespace = $objInstanceFromKnownObject->getNamespace();
                            if (empty($objInstanceFromKnownObjectNamespace)) {
                                $objInstanceFromKnownObjectNamespace = "no_namespace";
                            }
                            $this->createNode($objInstanceFromKnownObject->getName(), "class", array("namespace" => $objInstanceFromKnownObjectNamespace));
                            $this->createRelation($obj->getName(), "COMPOSE", $objInstanceFromKnownObject->getName());
                        } else {
                            $this->createNode($objInstance, "undiscovered_class", array("namespace" => "undiscovered_namespace"));
                            $this->createRelation($obj->getName(), "COMPOSE", $objInstance);
                        }
                    }
                }
            }
            if ($obj instanceof InterfaceDTO) {
                $this->createNode($obj->getName(), "interface", array("namespace" => $namespace));
            }
        }

        $this->rootNamespaceCollection = array_unique($this->rootNamespaceCollection);
        $this->realComponentQueryCollection = array_unique($this->realComponentQueryCollection);
        $this->nodeQueryCollection = array_unique($this->nodeQueryCollection);
        $this->relationQueryCollection = array_unique($this->relationQueryCollection);

        // Namespace > Component relations
        $flattenComponentNamespaces = implode(array_keys($this->componentNamespaceCollection));
        foreach($this->rootNamespaceCollection as $rootNamespace ){
            if (preg_match('/'.$rootNamespace.'/', $flattenComponentNamespaces)) {
                $componentName = $this->componentNamespaceCollection[$rootNamespace];
                $this->createNode($componentName, "component");
                $this->createRelation($rootNamespace, "DECLARED_IN", $componentName);
            }
        }

        $objects    = implode (' ', $this->nodeQueryCollection);
        $components = implode (' ', $this->realComponentQueryCollection);
        $relations  = implode (' ', $this->relationQueryCollection);

        var_dump($this->nodeQueryCollection);
        var_dump($this->realComponentQueryCollection);
        var_dump($this->relationQueryCollection);
//        exit;

        $this->query = $objects.$components.$relations;
    }
}


