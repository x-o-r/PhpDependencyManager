<?php

namespace PhpDependencyManager\DTO;

class ClassDTO implements DTOInterface
{
    private $className;
    private $namespace;
    private $rootNamespace;
    private $extend;

    private $uses = array();
    private $interfaces = array();
    private $injectedDependencies = array();
    private $classesInstances = array();

    /**
     * @return string
     * @throws DTOException
     */
    public function getName()
    {
        if ($this->className === null) {
            throw new DTOException(__CLASS__ . " : class name cannot be null");
        }
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setName($className)
    {
        $this->className = $className;
    }

    /**
     * @return string
     * @throws DTOException
     */
    public function getNamespace()
    {
        if ($this->namespace === null) {
            throw new DTOException(__CLASS__ . " : namespace cannot be null");
        }
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     * @throws DTOException
     */
    public function getRootNamespace()
    {
        if ($this->rootNamespace === null) {
            throw new DTOException(__CLASS__ . " : root namespace cannot be null");
        }
        return $this->rootNamespace;
    }

    /**
     * @param string $rootNamespace
     */
    public function setRootNamespace($rootNamespace)
    {
        $this->rootNamespace = $rootNamespace;
    }

    /**
     * @return string|null
     */
    public function getExtend()
    {
        return $this->extend;
    }

    /**
     * @param string $extend
     */
    public function setExtend($extend)
    {
        $this->extend = $extend;
    }

    /**
     * @return array
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * @param array $uses
     */
    public function setUses(array $uses)
    {
        $this->uses = $uses;
    }

    /**
     * @return array
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * @param array $interfaces
     */
    public function setInterfaces(array $interfaces)
    {
        $this->interfaces = $interfaces;
    }

    /**
     * @return array
     */
    public function getInjectedDependencies()
    {
        return $this->injectedDependencies;
    }

    /**
     * @param array $injectedDependencies
     */
    public function setInjectedDependencies(array $injectedDependencies)
    {
        $this->injectedDependencies = $injectedDependencies;
    }

    /**
     * @return array
     */
    public function getClassesInstances()
    {
        return $this->classesInstances;
    }

    /**
     * @param array $classesInstances
     */
    public function setClassesInstances(array $classesInstances)
    {
        $this->classesInstances = $classesInstances;
    }
}