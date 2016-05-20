<?php

namespace PhpDependencyManager\DataManager;

use PhpDependencyManager\StringFilter;
use PhpDependencyManager\DTO\ClassDTO;
use PhpDependencyManager\DTO\InterfaceDTO;
use PhpDependencyManager\DTO\ComponentDTO;

class DataManager
{
    private $client = null;

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
        array_push($this->query, $query);
    }

    public function createRelation($fromNode, $relationName, $toNode) {
        array_push($this->query, "CREATE (" . $fromNode . ")-[:" . $relationName . "{type : '" . $relationName . "'}]->(" . $toNode . ")");
    }

    private function getDTONameFromKey($key) {
        $className = explode(':', $key);
        return array_shift($className);
    }

//    public function createSchema(array $arrayclassesCollection, array $componentsCollection = null) {
//        $classCollection = array();
//        $interfaceCollection = array();
//        $namespaceCollection = array();
//        $componentCollection = array();
//        $componentCollectionByNamespace = array();
//        $effectiveNamespace = array();
//
//        $generateComponentData = false;
//        if (!is_null($componentsCollection) && is_array($componentsCollection)) {
//            $generateComponentData = true;
//        }
//
//        // First iteration on $classesCollection : extract entities ...
//        foreach ($arrayclassesCollection as $file) {
//
//            foreach ($file as $class) {
//                $namespace = str_replace('\\','_', StringFilter\StringFilter::removeChars($class->namespace, StringFilter\StringFilter::INVALID_SYMS));
//                $namespace = str_replace('-','', $namespace);
//                array_push($namespaceCollection, $namespace);
//                $class->namespace = $namespace;
//
//                $classname = StringFilter\StringFilter::removeChars($class->classname, StringFilter\StringFilter::INVALID_SYMS);
//                if ($class->type === "interface") {
//                    $interfaceCollection[$classname] = $namespace;
//                } else {
//                    if (count($class->interfaces))
//                    {
//                        foreach ($class->interfaces as $interface) {
//                            array_push($interfaceCollection, StringFilter\StringFilter::removeChars($interface, StringFilter\StringFilter::INVALID_SYMS));
//                        }
//                    }
//                    $classCollection[$classname] = $namespace;
//                }
//
//
//
//            }
//        }
//
//        $namespaceCollection = array_unique($namespaceCollection);
//        $interfaceCollection = array_unique($interfaceCollection);
//
//        // .. and create them
//        foreach($classCollection as $key => $value)
//        {
//            $this->createNode($key,"class", array('namespace' => $value));
//        }
//        array_map(function($val){$this->createNode($val,"interface");}, $interfaceCollection);
//
//
//        // First iteration on $componentsCollection : extract entities ...
//        if ($generateComponentData) {
//            foreach ($componentsCollection as $component)
//            {
//                foreach($component->namespaces as $namespace)
//                {
//                    $namespace = str_replace('\\', '_', $namespace);
//                    $namespace = str_replace('-', '', $namespace);
//                    $namespace = rtrim($namespace, '_');
//                    $componentCollectionByNamespace[$namespace] = $component->name;
//                }
//
//            }
//
//            // Create namespace entities only if it used by a class
//            $namespaceCollectionToString = implode($namespaceCollection);
//            foreach (array_keys($componentCollectionByNamespace) as $namespace)
//            {
//                if (preg_match('/' . $namespace . '/', $namespaceCollectionToString)) {
//                    array_push($effectiveNamespace, $namespace);
//                }
//            }
//            $componentCollection = array_unique($componentCollection);
//            $componentCollectionByNamespace = array_unique($componentCollectionByNamespace);
//            $effectiveNamespace = array_unique($effectiveNamespace);
//
//            // .. and create them
//            array_map(function($val){$this->createNode($val,"namespace");}, $effectiveNamespace);
//            array_map(function($val){$this->createNode($val,"component");}, $componentCollection);
//        } else
//        {
//            array_map(function($val){$this->createNode($val,"namespace");}, $namespaceCollection);
//        }
//
//        // Second iteration on $classesCollection : extract relations and create them
//        foreach ($arrayclassesCollection as $file)
//        {
//            foreach ($file as $class)
//            {
//                $className = StringFilter\StringFilter::removeChars($class->classname, StringFilter\StringFilter::INVALID_SYMS);
//                $classNamespace = StringFilter\StringFilter::removeChars($class->namespace, StringFilter\StringFilter::INVALID_SYMS);
//                $classExtend = StringFilter\StringFilter::removeChars($class->extend, StringFilter\StringFilter::INVALID_SYMS);
//
//                // Namespaces
//                if(!is_null($classNamespace))
//                {
//                    $namespaceArray = $generateComponentData ? $effectiveNamespace : $namespaceCollection;
//                    if (array_key_exists($classNamespace, $namespaceArray)) {
//                        $this->createRelation($className, "HAS", $namespaceArray[$classNamespace]);
//                    }
//                }
//
//                if(!empty($classExtend)) {
//                    if (!in_array($classExtend, $classCollection)) {
//                        $this->createNode($classExtend, "undiscovered_class");
//                        array_push($classCollection, $classExtend);
//                    }
//                    $this->createRelation($className, "EXTENDS", $classExtend);
//                }
//
//                if (count($class->interfaces)) {
//                    foreach ($class->interfaces as $interface) {
//                        $interface = StringFilter\StringFilter::removeChars($interface, StringFilter\StringFilter::INVALID_SYMS);
//                        if (!in_array($interface, $interfaceCollection))
//                        {
//                            $this->createNode($interface, "undiscovered_interface");
//                            array_push($interfaceCollection, $interface);
//                        }
//                        $this->createRelation($className, "IMPLEMENTS", $interface);
//                    }
//                }
//                if (count($class->classesInstances)) {
//                    foreach ($class->classesInstances as $instanciated) {
//                        $instanciated = StringFilter\StringFilter::removeChars($instanciated, StringFilter\StringFilter::INVALID_SYMS);
//                        if (!in_array($instanciated, $classCollection))
//                        {
//                            $this->createNode($instanciated, "undiscovered_class");
//                            array_push($classCollection, $instanciated);
//                        }
//                        $this->createRelation($className, "COMPOSES", $instanciated);
//                    }
//                }
//                if (count($class->injectedDependencies)) {
//                    foreach ($class->injectedDependencies as $injected) {
//                        $injected =  $interface = StringFilter\StringFilter::removeChars($injected, StringFilter\StringFilter::INVALID_SYMS);
//                        if (!in_array($injected, $classCollection))
//                        {
//                            $this->createNode($injected, "undiscovered_class");
//                            array_push($classCollection, $injected);
//                        }
//                        $this->createRelation($injected, "AGGREGATES", $className);
//                    }
//                }
//            }
//        }
//
//        // Create relations between components and effective namespace
//        if ($generateComponentData)
//        {
//            foreach (array_keys($componentCollectionByNamespace) as $namespace)
//            {
//                if (array_key_exists($namespace, $effectiveNamespace)) {
//                    $this->createRelation($namespace, "DECLAREDBY", $componentCollectionByNamespace[$namespace]);
//                }
//            }
//        }
//
//        var_dump($classCollection);
//        echo "------\n";
//        var_dump($namespaceCollection);
//        echo "------\n";
//        var_dump($effectiveNamespace);
//        echo "------\n";
//        var_dump($arrayclassesCollection);
//        echo "------\n";
//        var_dump($componentCollection);
//        echo "------\n";
//        var_dump($componentCollectionByNamespace);
//        exit;
//
//        var_dump($this->getQuery());exit;
//        if ($this->client !== null) {
//            $this->client->sendCypherQuery($this->query);
//        }
//    }

    public function createSchema(array $objectDTOCollection, array $componentDTOCollection = null) {

        $classNameToID = array();
        foreach (array_keys($objectDTOCollection) as $objectKey) {
            $classNameToID[$this->getDTONameFromKey($objectKey)][$objectKey]=$objectKey;
        }

        foreach (array_keys($objectDTOCollection) as $objectKey) {
            $obj = $objectDTOCollection[$objectKey];
            if ($obj instanceof ClassDTO) {
                $this->createNode($obj->getName(), "class", array('namespace' => $obj->getNamespace()));
                $classInstances = $obj->getClassesInstances();

                if (!empty($classInstances) && count($classInstances)) { // Compose
                    foreach ($classInstances as $objInstance) {
                        if (key_exists($objInstance, $classNameToID) && key_exists($objectKey, $classNameToID[$objInstance])) {
                            $objInstanceFromKnownObject = $classNameToID[$objInstance][$objectKey];

                            $this->createNode($objInstanceFromKnownObject->getName(), "class", array('namespace' => $objInstanceFromKnownObject->getNamespace()));
                            $this->createRelation($obj->getName(), "COMPOSE", $objInstanceFromKnownObject->getName());
                        } else {
                            $this->createNode($objInstance, "undiscovered_class", array('namespace' => 'undiscovered_namespace'));
                            $this->createRelation($obj->getName(), "COMPOSE", $objInstance);
                        }

                    }
                }
            }
            if (!empty($obj->getExtend())) { // Aggregate

            }
        }
        //        var_dump($this->query);
    }
}


