<?php

namespace PhpDependencyManager\DataManager;

use PhpDependencyManager\DTO\ClassDTO;
use PhpDependencyManager\DTO\InterfaceDTO;
use PhpDependencyManager\StringFilter;

class DataManager
{
    private $client = null;
    private $query = null;
    private $classNameToID = array();
    private $rootNamespaceCollection = array(); // Key : 'root namespace', value : 'component name'
    private $componentNamespaceCollection = array();
    private $fullComponentCollection = array();
    private $namespaceCollection = array();

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
                array_push($this->namespaceCollection, $part);
                // Create node
                $this->createNode($part, "namespace");//, array("full_namespace" => $namespace));
                if (is_null($previousPart)) { // Create component root namespace relation
                    array_push($this->rootNamespaceCollection, $part);
                    $previousPart = $part;
                } else { // If previous namespace node exists, create relation and save previous node
                    $this->createRelation($part, "IS_IN_NS", $previousPart);
                    $previousPart = $part;
                }
            }
        }
    }

    public function createNodeFromUndiscoveredObject($unknownObject, $object, $objectKey, $relationType, $reverseRelation = null){
        if ($reverseRelation === null){
            $reverseRelation = false;
        } else {
            $reverseRelation = false;
        }
        // Could be optimised with pattern search in flatten $this->classNameToID keys
        if (key_exists($unknownObject, $this->classNameToID) && key_exists($objectKey, $this->classNameToID[$unknownObject])) {
            $objInstanceFromKnownObject = $this->classNameToID[$unknownObject][$objectKey];
            $objInstanceFromKnownObjectNamespace = $objInstanceFromKnownObject->getNamespace();
            if (empty($objInstanceFromKnownObjectNamespace)) {
                $objInstanceFromKnownObjectNamespace = "no_namespace";
            }
            $objectType = null;
            if ($object instanceof ClassDTO) {
                $objectType = "class";
            }
            if ($object instanceof InterfaceDTO) {
                $objectType = "interface";
            }
            $this->createNode($objInstanceFromKnownObject->getName(), $objectType, array("namespace" => $objInstanceFromKnownObjectNamespace));
            if ($reverseRelation === false){
                $this->createRelation($object->getName(), $relationType, $objInstanceFromKnownObject->getName());
            } else{
                $this->createRelation($objInstanceFromKnownObject->getName(), $relationType, $object->getName());
            }

        } else {
            $this->createNode($unknownObject, "undiscovered_class", array("namespace" => "undiscovered_namespace"));
            if ($reverseRelation === false){
                $this->createRelation($object->getName(), $relationType, $unknownObject);
            } else{
                $this->createRelation($unknownObject, $relationType, $object->getName());
            }
        }
    }

    public function createSchema(array $objectDTOCollection, array $componentDTOCollection = null) {

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

            $object = $objectDTOCollection[$objectKey];
            $namespace = $object->getNamespace();

            if (empty($namespace)){
                $namespace = "no_namespace";
            } else{
                $this->createNamespace($namespace);
            }

            if ($object instanceof InterfaceDTO) {
                $this->createNode($object->getName(), "interface", array("namespace" => $namespace));
            }

            if ($object instanceof ClassDTO) {
                $this->createNode($object->getName(), "class", array("namespace" => $namespace));
                $classInstances = $object->getClassesInstances();
                if (is_array($classInstances) && count($classInstances)) { // Compose
                    foreach ($classInstances as $objInstance) {
                        $this->createNodeFromUndiscoveredObject($objInstance, $object, $objectKey, "COMPOSE");
//                        if (key_exists($objInstance, $classNameToID) && key_exists($objectKey, $classNameToID[$objInstance])) {
//                            $objInstanceFromKnownObject = $classNameToID[$objInstance][$objectKey];
//                            $objInstanceFromKnownObjectNamespace = $objInstanceFromKnownObject->getNamespace();
//                            if (empty($objInstanceFromKnownObjectNamespace)) {
//                                $objInstanceFromKnownObjectNamespace = "no_namespace";
//                            }
//                            $this->createNode($objInstanceFromKnownObject->getName(), "class", array("namespace" => $objInstanceFromKnownObjectNamespace));
//                            $this->createRelation($obj->getName(), "COMPOSE", $objInstanceFromKnownObject->getName());
//                        } else {
//                            $this->createNode($objInstance, "undiscovered_class", array("namespace" => "undiscovered_namespace"));
//                            $this->createRelation($obj->getName(), "COMPOSE", $objInstance);
//                        }
                    }
                }
                if (is_array($object->getInjectedDependencies()) && count($object->getInjectedDependencies())) { // Aggregate
                    foreach($object->getInjectedDependencies() as $injectedDependencies) {
                        $this->createNodeFromUndiscoveredObject($injectedDependencies, $object, $objectKey, "AGGREGATE", true);
                    }
                }

                if (is_array($object->getInterfaces()) && count($object->getInterfaces())) { // Implement
                    foreach($object->getInterfaces() as $interface){
                        $this->createNodeFromUndiscoveredObject($interface, $object, $objectKey, "IMPLEMENT");
                    }
                }
            }

            if (!empty($object->getExtend())) { // Extend
                $this->createNodeFromUndiscoveredObject($object->getExtend(), $object, $objectKey, "EXTEND");
            }

            // Object > namespace
            $namespaceParts = explode('_', $namespace);
            $terminalNamespace = end($namespaceParts);
            if(in_array($terminalNamespace, $this->namespaceCollection)){
                $this->createRelation($object->getName(), "HAS_NS", $terminalNamespace);
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
                $this->createRelation($rootNamespace, "DECLARED_IN_PKG", $componentName);
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


