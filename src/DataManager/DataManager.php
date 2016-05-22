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
    private $rootNamespaceCollection = array();
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

    public function dumpObjectAndRelation(){
        return  implode("\n", $this->nodeQueryCollection) . "\n" .
                implode("\n", $this->realComponentQueryCollection) . "\n" .
                implode("\n", $this->relationQueryCollection);
    }

    public function dropSchema() {
        $this->client->sendCypherQuery("MATCH (n) detach delete n");
    }

    public function createNode($nodeName, $nodeType, $attributes = null) {
        $query = "CREATE (`" . $nodeName . "`:" . $nodeType . "{name:'" . $nodeName . "',";
        if (is_array($attributes) && count($attributes)){
            foreach ($attributes as $key => $value){
                $query .= str_replace('"', '', $key) . ":'" . str_replace('"', '', $value) . "',";
            }
        }
        $query = substr($query, 0, -1);
        $query .= '})';

        if ($nodeType === "class" || $nodeType === "interface" || $nodeType === "namespace" || $nodeType === "undiscovered_class"){
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

    public function createUndiscoveredObject($unknownObjectName, $object, $objectKey, $relationType, $reverseRelation = null){
        if ($reverseRelation === null) {
            $reverseRelation = false;
        } else {
            $reverseRelation = true;
        }

        if (in_array($unknownObjectName, $this->classNameToID)) {
//            if (array_key_exists($unknownObjectName, $this->classNameToID[$unknownObjectName]))
//            $existingObject = $this->classNameToID[$unknownObjectName];
//            foreach(array_keys($existingObject) as $id){
//
//            }
//            // Retrieve unique object : unknown object has the same namespace than $object or namespace is in $unknownObjectName string
//            $uniqueObjectKey = $unknownObjectName . ":" . $object->getNamespace();
//
//            var_dump($object, $uniqueObjectKey); exit;
//
//            $objInstanceFromKnownObject = $this->classNameToID[$unknownObjectName][$objectKey];
//            $objInstanceFromKnownObjectNamespace = $objInstanceFromKnownObject->getNamespace();
//            if (empty($objInstanceFromKnownObjectNamespace)) {
//                $objInstanceFromKnownObjectNamespace = "no_namespace";
//            }
//            $objectType = null;
//            if ($object instanceof ClassDTO) {
//                $objectType = "class";
//            }
//            if ($object instanceof InterfaceDTO) {
//                $objectType = "interface";
//            }
//            $this->createNode($objInstanceFromKnownObject->getName(), $objectType, array("namespace" => $objInstanceFromKnownObjectNamespace));
//            if ($reverseRelation === false){
//                $this->createRelation($object->getName(), $relationType, $objInstanceFromKnownObject->getName());
//            } else{
//                $this->createRelation($objInstanceFromKnownObject->getName(), $relationType, $object->getName());
//            }
            if ($reverseRelation === false){
                $this->createRelation($object->getName(), $relationType, $unknownObjectName);
            } else{
                $this->createRelation($unknownObjectName, $relationType, $object->getName());
            }
        } else {
            $this->createNode($unknownObjectName, "undiscovered_class", array("namespace" => "undiscovered_namespace"));
            if ($reverseRelation === false){
                $this->createRelation($object->getName(), $relationType, $unknownObjectName);
            } else{
                $this->createRelation($unknownObjectName, $relationType, $object->getName());
            }
        }
    }

    public function createSchema(array $objectDTOCollection, array $componentDTOCollection = null) {
//        foreach (array_keys($objectDTOCollection) as $objectKey) {
//            $nonUniqueObjectName = $this->getDTONameFromKey($objectKey);
//            if (!array_key_exists($nonUniqueObjectName, $this->classNameToID)){
//                $this->classNameToID[$nonUniqueObjectName] = array();
//            }
//            array_push($this->classNameToID[$nonUniqueObjectName], array($objectKey => $objectDTOCollection[$objectKey]));
//        }
//        foreach (array_keys($objectDTOCollection) as $objectKey) {
//            $object = $objectDTOCollection[$objectKey];
//            $this->classNameToID[$object->getname() . ":" . $object->getNamespace()] = $object;
//        }
        var_dump($objectDTOCollection); exit;
        foreach (array_keys($objectDTOCollection) as $objectKey) {
            array_push($this->classNameToID, $objectKey);
        }

        // Componnent
        if (!is_null($componentDTOCollection)) {
            foreach ($componentDTOCollection as $component) {
                $this->fullComponentCollection[$component->getName()] = $component;

                if (!empty($component->getNamespaces())) {
                    foreach ($component->getNamespaces() as $rootNamespace) {
                        $this->componentNamespaceCollection[$rootNamespace] = $component->getName();
                    }
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
                $this->createNode($object->getNamespace().'>'.$object->getName(), "interface", array("namespace" => $namespace));
            }

            if ($object instanceof ClassDTO) {
                $this->createNode($object->getNamespace().'>'.$object->getName(), "class", array("namespace" => $namespace));
                $classInstances = $object->getClassesInstances();
                if (is_array($classInstances) && count($classInstances)) { // Compose
                    foreach ($classInstances as $objInstance) {
                        $this->createUndiscoveredObject($objInstance, $object, $objectKey, "COMPOSE");
                    }
                }
                if (is_array($object->getInjectedDependencies()) && count($object->getInjectedDependencies())) { // Aggregate
                    foreach($object->getInjectedDependencies() as $injectedDependencies) {
                        $this->createUndiscoveredObject($injectedDependencies, $object, $objectKey, "AGGREGATE", true);
                    }
                }

                if (is_array($object->getInterfaces()) && count($object->getInterfaces())) { // Implement
                    foreach($object->getInterfaces() as $interface){
                        $this->createUndiscoveredObject($interface, $object, $objectKey, "IMPLEMENT");
                    }
                }
            }

            if (!empty($object->getExtend())) { // Extend
                $this->createUndiscoveredObject($object->getExtend(), $object, $objectKey, "EXTEND");
            }

            // Object > namespace
            $namespaceParts = explode('_', $namespace);
            $terminalNamespace = end($namespaceParts);
            if(in_array($terminalNamespace, $this->namespaceCollection)){
                $this->createRelation($object->getName(), "HAS_NS", $terminalNamespace);
            }
        }

        if (!is_null($componentDTOCollection)) {
            $flattenComponentNamespaces = implode(array_keys($this->componentNamespaceCollection));
            foreach (array_unique($this->rootNamespaceCollection) as $rootNamespace) {
                if (preg_match('/' . $rootNamespace . '/', $flattenComponentNamespaces)) {
                    $componentName = $this->componentNamespaceCollection[$rootNamespace];
                    $this->createNode($componentName, "component");
                    $this->createRelation($rootNamespace, "DECLARED_IN_PKG", $componentName);
                }
            }
        }

        $objects    = implode (' ', array_unique($this->nodeQueryCollection));
        $components = implode (' ', array_unique($this->realComponentQueryCollection));
        $relations  = implode (' ', array_unique($this->relationQueryCollection));

        $this->query = $objects.$components.$relations;
    }
}


