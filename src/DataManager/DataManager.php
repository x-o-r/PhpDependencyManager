<?php

namespace PhpDependencyManager\DataManager;

use PhpDependencyManager\DTO\ClassDTO;
use PhpDependencyManager\DTO\InterfaceDTO;
use PhpDependencyManager\StringFilter;

class DataManager
{
    private $client = null;
    private $query = null;
    private $existingObjects = array();
    private $rootNamespaceCollection = array();
    private $componentNamespaceCollection = array();
    private $fullComponentCollection = array();
    private $realComponentQueryCollection = array();
    private $nodeQueryCollection = array();
    private $relationQueryCollection = array();
    private $undiscoveredObject = array();
    private $createdNamespace = array();

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
        return  implode("\n", array_values($this->nodeQueryCollection)) . "\n" .
                implode("\n", array_values($this->realComponentQueryCollection)) . "\n" .
                implode("\n", array_values($this->relationQueryCollection));
    }

    public function dropSchema() {
        $this->client->sendCypherQuery("MATCH (n) detach delete n");
    }

    public function createNode($nodeName, $nodeType, $attributes = null) {
        $query = "CREATE (`" . $nodeName . "`:" . $nodeType;
        if (is_array($attributes) && count($attributes)){
            $query .= "{";
            foreach ($attributes as $key => $value){
                $query .= str_replace('"', '', $key) . ":'`" . str_replace('"', '', str_replace('\\', '\\\\', $value)) . "`',";
            }
            $query = substr($query, 0, -1); // Remove last ','
            $query .= '}';
        }
        $query .= ')';

        if ($nodeType === "class" || $nodeType === "interface" || $nodeType === "namespace" || $nodeType === "undiscovered_class"){
            $this->nodeQueryCollection[$nodeName] = $query;
        }
        if ($nodeType === "component"){
            $this->realComponentQueryCollection[$nodeName] = $query;
        }
    }

    public function createRelation($fromNode, $relationName, $toNode) {
        $this->relationQueryCollection[$fromNode.$relationName.$toNode] = "CREATE (`" . $fromNode . "`)-[:" . $relationName . "{type:'" . $relationName . "'}]->(`" . $toNode . "`)";
    }

    public function createNamespace($namespace) {
        if (!empty($namespace)) {
            if(!in_array($namespace, $this->createdNamespace)) {
                $namespaceParts = explode('\\', $namespace);
                $fullNamespace = null;
                $previousNamespace = null;
                foreach ($namespaceParts as $part) {
                    if(empty($fullNamespace)){
                        $fullNamespace = $part;
                        $this->createNode($fullNamespace, "namespace", array('name' => $part));
                    } else{
                        $nodeName = $fullNamespace . '\\' . $part;
                        $this->createNode($nodeName, "namespace", array('name' => $part));
                        $this->createRelation($nodeName, "IS_IN_NS", $fullNamespace);
                        $fullNamespace = $nodeName;
                    }
                    array_push($this->createdNamespace, $fullNamespace);
                }
            }
        }
    }

    public function createUndiscoveredObject($objectName, $contextObject, $relationType, $reverseRelation = null) {
        if ($reverseRelation === null) {
            $reverseRelation = false;
        } else {
            $reverseRelation = true;
        }

        // Check if class name has a namespace
        $instanceContainsNamespace = preg_match('/\\\\/', $objectName);

        if ($instanceContainsNamespace) { // Class name contains namespace and exists in $this->existingObjects
            $objectName = ltrim('\\', $objectName);
            if (array_key_exists($objectName, $this->existingObjects)) {
                $this->createRelation( $this->existingObjects[$objectName]->getname(), $relationType, $objectName);
                return;
            }
        } else if (array_key_exists($contextObject->getNamespace() . '\\' . $objectName, $this->existingObjects)) { // Class name exists in current object's namespace
            $this->createRelation($contextObject->getNamespace() . '\\' . $contextObject->getName(), $relationType, $contextObject->getNamespace() . '\\' . $objectName);
            return;
        } else {  // Check uses and aliases
            $uses = $contextObject->getUses();
            if (array_key_exists($objectName, $uses)) {
                if (array_key_exists($uses[$objectName], $this->existingObjects)) {
                    var_dump($uses[$objectName]);
                    $existingObject = $this->existingObjects[$uses[$objectName]];
                    $this->createRelation($contextObject->getNamespace() . '\\' . $contextObject->getName(), $relationType, $existingObject->getNamespace() . '\\' . $existingObject->getName()); // Could be replaced by just $instance ?
                    return;
                }
            }
        }

        // Finally, $instance was not discovered, create it has undiscovered class
        // If $instance contains a namespace, add it to his attributes list
        if (!in_array($objectName, $this->undiscoveredObject)) {
            if ($instanceContainsNamespace) {
                $parts = explode('\\', $objectName);
                $namespace = $parts[0];
                for ($i = 1; $i < count($parts) - 1; $i++) {
                    $namespace .= $parts[$i];
                }
                $objectName = end($parts);
                $this->createNode($objectName, "undiscovered_class", array('name' => $objectName, 'namespace' => $namespace));
            } else {
                $this->createNode($objectName, "undiscovered_class", array('name' => $objectName));
            }
            array_push($this->undiscoveredObject, $objectName);
        }
        $this->createRelation($contextObject->getNamespace() . '\\' . $contextObject->getName(), $relationType, $objectName);
    }

    public function createSchema(array $objectDTOCollection, array $componentDTOCollection = null) {

        foreach (array_keys($objectDTOCollection) as $objectKey) {
            $objectParts = explode('\\', $objectKey);
            $objectKeyWithoutNamespace = end($objectParts);
            $object = $objectDTOCollection[$objectKey];
            $objectNamespace = $object->getNamespace();
            if (empty($objectNamespace)){
                continue;
            }
            $this->existingObjects[$objectKey] = $object;
            $this->rootNamespaceCollection[$object->getRootNamespace()] = null; // Only for using the unicity of a sorted map keys

            if ($object instanceof ClassDTO) {
                $this->createNode($objectKey, "class", array ('name' => $objectKeyWithoutNamespace, 'namespace' => $objectNamespace));

            }
            if ($object instanceof InterfaceDTO) {
                $this->createNode($objectKey, "interface", array ('name' => $objectKeyWithoutNamespace, 'namespace' => $objectNamespace));
            }
            
            $this->createNamespace($objectNamespace);
            $this->createRelation($objectKey, "HAS_NS", $objectNamespace);
        }

        foreach ($this->existingObjects as $object) {

            if ($object instanceof ClassDTO) {
                $classesInstances = $object->getClassesInstances();
                foreach($classesInstances as $instance) {
                    $this->createUndiscoveredObject($instance, $object, "COMPOSE");
                }

                $interfacesImplemented = $object->getInterfaces();
                foreach($interfacesImplemented as $interface) {
                    $this->createUndiscoveredObject($interface, $object, "IMPLEMENT");
                }

                $injectedDependencies = $object->getInjectedDependencies();
                foreach($injectedDependencies as $injectedDependency) {
                    $this->createUndiscoveredObject($injectedDependency, $object, "AGGREGATE", true);
                }
            }

            // Handle object extension
            if (!empty($object->getExtend())) {
                $this->createUndiscoveredObject($object->getExtend(), $object, "EXTEND");
            }
        }

        // Component
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

        if (!is_null($this->fullComponentCollection)) {
            foreach (array_keys($this->rootNamespaceCollection) as $rootNamespace) {
                if (array_key_exists($rootNamespace, $this->componentNamespaceCollection)) {
                    $componentName = $this->componentNamespaceCollection[$rootNamespace];
                    $component = $this->fullComponentCollection[$componentName];
                    $this->createNode($componentName, "component", array('name' => $component->getSubName()));
                    $this->createRelation($rootNamespace, "DECLARED_IN_PKG", $componentName);
                }
            }
        }

        // Still usefull ?
        $objects    = implode (' ', array_values($this->nodeQueryCollection));
        $components = implode (' ', array_values($this->realComponentQueryCollection));
        $relations  = implode (' ', array_values($this->relationQueryCollection));

        $this->query = $objects.$components.$relations;
    }
}


