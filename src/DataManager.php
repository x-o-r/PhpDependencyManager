<?php

namespace PhpDependencyManager;

class DataManager
{
    private $client = null;

    public function __construct($neo4jClient)
    {
        $this->client = $neo4jClient;
        $this->query = null;
    }

    public function getQuery() {
        return $this->query;
    }

    public function dropSchema() {
        $this->client->sendCypherQuery("MATCH (n) detach delete n");
    }

    public function createNode($nodeName, $nodeType) {
        $this->query .= "CREATE (" . $nodeName . ":" . $nodeType . " { name : '" . $nodeName . "' })\n";
    }

    public function createRelation($fromNode, $relationName, $toNode) {
        $this->query .= "CREATE (" . $fromNode . ")-[:" . $relationName . "{type : '" . $relationName . "'}]->(" . $toNode . ")\n";
    }

    public function createSchema(array $classesCollection, array $componentsCollection = null) {
        $classCollection = array();
        $interfaceCollection = array();
        $namespaceCollection = array();

        // First iteration on $classesCollection : extract entities ...
        foreach ($classesCollection as $file) {
            foreach ($file as $class) {
                if ($class->type === "interface")
                {
                    array_push($interfaceCollection, $class->classname);
                } else
                {
                    array_push($classCollection, $class->classname);
                }
                array_push($namespaceCollection, $class->namespace);
                if (count($class->interfaces))
                {
                    foreach ($class->interfaces as $interface) {
                        array_push($interfaceCollection, $interface);
                    }
                }
            }
        }

        $namespaceCollection = array_unique($namespaceCollection);
        $interfaceCollection = array_unique($interfaceCollection);

        // .. and create them
        array_map(function($val){$this->createNode($val,"class");}, $classCollection);
        array_map(function($val){$this->createNode($val,"interface");}, $interfaceCollection);
        array_map(function($val){$this->createNode($val,"namespace");}, $namespaceCollection);
        array_map(function($val){$this->createNode($val,"component");}, $componentsCollection);
        
        // Second iteration on $classesCollection : extract relations and create them
        foreach ($classesCollection as $file)
        {
            foreach ($file as $class)
            {
                if(!is_null($class->namespace))
                    $this->createRelation($class->classname, "HAS", $class->namespace);

                if(!is_null($class->extend))
                    $this->createRelation($class->classname, "EXTENDS", $class->extend);

                if (count($class->interfaces)) {
                    foreach ($class->interfaces as $interface) {
                        $this->createRelation($class->classname, "IMPLEMENTS", $interface);
                    }
                }
                if (count($class->classesInstances)) {
                    foreach ($class->classesInstances as $instanciated) {
                        if (!in_array($instanciated, $classCollection))
                        {
                            $this->createNode($instanciated, "undiscovered_class");
                            array_push($classCollection, $instanciated);
                        }
                        $this->createRelation($class->classname, "COMPOSES", $instanciated);
                    }
                }
                if (count($class->injectedDependencies)) {
                    foreach ($class->injectedDependencies as $injected) {
                        if (!in_array($injected, $classCollection))
                        {
                            $this->createNode($injected, "undiscovered_class");
                            array_push($classCollection, $injected);
                        }
                        $this->createRelation($injected, "AGGREGATES", $class->classname);
                    }
                }
            }
        }

        // Create relations between components
        foreach ($componentsCollection as $component)
        {

        }

//        var_dump($this->getQuery());exit;
        if ($this->client !== null)
            $this->client->sendCypherQuery($this->query);
    }
}


