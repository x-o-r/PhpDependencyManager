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

    private function createNode($nodeName, $nodeType, $attributes = null) {
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

        if ($nodeType === "class" || $nodeType === "interface" || $nodeType === "namespace" || $nodeType === "undiscovered_object"){
            $this->nodeQueryCollection[$nodeName] = $query;
        }
        if ($nodeType === "component"){
            $this->realComponentQueryCollection[$nodeName] = $query;
        }
    }

    private function createRelation($fromNode, $relationName, $toNode) {
        $this->relationQueryCollection[$fromNode.$relationName.$toNode] = "CREATE (`" . $fromNode . "`)-[:" . $relationName . "{type:'" . $relationName . "'}]->(`" . $toNode . "`)";
    }

    private function createNamespace($namespace) {
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

    private function createRelationHelper($from, $relationType, $to){
        if ($relationType === "AGGREGATE"){
            $this->createRelation($to, $relationType, $from);
        } else{
            $this->createRelation($from, $relationType, $to);
        }
    }

    private function createUndiscoveredObject($objectName, $contextObject, $relationType) {

        $fullObjectName = $contextObject->getNamespace() . '\\' . $contextObject->getName();

        // Check if class name has a namespace
        $instanceContainsNamespace = preg_match('/\\\\/', $objectName);

        if ($instanceContainsNamespace) { // Class name contains namespace and exists in $this->existingObjects

            if (array_key_exists($objectName, $this->existingObjects)) { // Full namespace is specified in new
                $fullInstanciatedname = $this->existingObjects[$objectName]->getNameSpace() . '\\' . $this->existingObjects[$objectName]->getName();
                $this->createRelationHelper($fullObjectName, $relationType, $fullInstanciatedname);
                return;
            } else {
                if (array_key_exists($contextObject->getNamespace() . '\\'. $objectName, $this->existingObjects)) { // Object namespace + full instanciated name
                    $srcObject = $this->existingObjects[$contextObject->getNamespace() . '\\'. $objectName];
                    $fullInstanciatedName = $srcObject->getNamespace() . '\\'. $srcObject->getname();
                    $this->createRelationHelper($fullObjectName, $relationType, $fullInstanciatedName);
                    return;
                }
                foreach ($contextObject->getUses() as $use){
                    if (array_key_exists($use.'\\'.$objectName, $this->existingObjects)){ // Current Use + $objectName
                        $srcObject = $this->existingObjects[$use.'\\'.$objectName];
                        $fullInstanciatedName = $srcObject->getNamespace() . '\\'. $srcObject->getname();
                        $this->createRelationHelper($fullObjectName, $relationType, $fullInstanciatedName);
                        return;
                    } else {
                        $useParts = explode('\\', $use);
                        $newParts = explode('\\', $objectName);
                        if($useParts[count($useParts)-1] == $newParts[0]){ // final part of current use and first part of $objectName
                            $fullParts = implode('\\', array_unique(array_merge($useParts, $newParts)));
                            if(array_key_exists($fullParts, $this->existingObjects)){
                                $srcObject = $this->existingObjects[$fullParts];
                                $fullInstanciatedName = $srcObject->getNamespace() . '\\'. $srcObject->getname();
                                $this->createRelationHelper($fullObjectName, $relationType, $fullInstanciatedName);
                                return;
                            }
                        }
                    }
                }
            }
        } else if (array_key_exists($contextObject->getNamespace() . '\\' . $objectName, $this->existingObjects)) { // Class name exists in current object's namespace
            $this->createRelationHelper($fullObjectName, $relationType, $contextObject->getNamespace() . '\\' . $objectName);
            return;
        } else {  // Check uses and aliases
            $uses = $contextObject->getUses();
            if (array_key_exists($objectName, $uses)) {
                if (array_key_exists($uses[$objectName], $this->existingObjects)) {
                    $existingObject = $this->existingObjects[$uses[$objectName]];
                    $this->createRelationHelper($fullObjectName, $relationType, $existingObject->getNamespace() . '\\' . $existingObject->getName());
                    return;
                }
            }
        }

        // Finally, $instance was not discovered, create it as undiscovered class
        // If $instance contains a namespace, add it to his attributes list
        if (!in_array($objectName, $this->undiscoveredObject)) {
            if ($instanceContainsNamespace) {
                $parts = explode('\\', $objectName);
                $namespace = $parts[0];
                for ($i = 1; $i < count($parts) - 1; $i++) {
                    $namespace .= $parts[$i];
                }
                $objectName = end($parts);
                $this->createNode($objectName, "undiscovered_object", array('name' => $objectName, 'namespace' => $namespace));
            } else {
                $this->createNode($objectName, "undiscovered_object", array('name' => $objectName));
            }
            array_push($this->undiscoveredObject, $objectName);
        }
        $this->createRelation($contextObject->getNamespace() . '\\' . $contextObject->getName(), $relationType, $objectName);
    }

    public function createSchema(array $objectDTOCollection, array $componentDTOCollection = null) {

//        var_dump($objectDTOCollection);exit;
        foreach (array_keys($objectDTOCollection) as $objectKey) {
            $object = $objectDTOCollection[$objectKey];
            $objectNamespace = $object->getNamespace();
            if (empty($objectNamespace)){
                continue;
            }
            $this->existingObjects[$objectKey] = $object;
            $this->rootNamespaceCollection[$object->getRootNamespace()] = null; // Only for using the unicity of a sorted map keys

            if ($object instanceof ClassDTO) {
                $this->createNode($objectKey, "class", array ('name' => $object->getName(), 'namespace' => $objectNamespace));

            }
            if ($object instanceof InterfaceDTO) {
                $this->createNode($objectKey, "interface", array ('name' => $object->getName(), 'namespace' => $objectNamespace));
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
            }

            $injectedDependencies = $object->getInjectedDependencies();
            foreach($injectedDependencies as $injectedDependency) {
                $this->createUndiscoveredObject($injectedDependency, $object, "AGGREGATE");
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

                foreach ($component->getNamespaces() as $rootNamespace) {
                    $this->componentNamespaceCollection[$rootNamespace] = $component->getName();
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

            // Component require another component
            foreach ($componentDTOCollection as $component) {
                foreach ($component->getRequires() as $requireName => $version) {
                    if (array_key_exists($requireName, $this->fullComponentCollection)){
                        $this->createRelation($component->getName(), "REQUIRE",  $this->fullComponentCollection[$requireName]->getName());
                    }
                }
            }
        }

        $objects    = implode (' ', array_values($this->nodeQueryCollection));
        $components = implode (' ', array_values($this->realComponentQueryCollection));
        $relations  = implode (' ', array_values($this->relationQueryCollection));

        $this->query = $objects.$components.$relations;
    }
}


