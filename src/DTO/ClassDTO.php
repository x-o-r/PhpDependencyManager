<?php

namespace PhpDependencyManager\DTO;

class ClassDTO implements ObjectDTOInterface
{
    private $className = null;
    private $namespace = null;
    private $rootNamespace = null;

    private $extend = null;
    private $interfaces = array();
    private $injectedDependencies = array();
    private $classesInstances = array();
    private $uses = array();
    private $aliases = array();

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
    public function setUses($uses)
    {
        $this->uses = $uses;
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @param array $aliases
     */
    public function setAliases($aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->className;
    }

    /**
     * @param mixed $className
     */
    public function setName($className)
    {
//        $this->className = $className, StringFilter::INVALID_SYMS;
        $this->className = $className;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getExtend()
    {
        return $this->extend;
    }

    /**
     * @param mixed $extend
     */
    public function setExtend($extend)
    {
        $this->extend = $extend;
    }

    /**
     * @return mixed
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * @param mixed $interfaces
     */
    public function setInterfaces($interfaces)
    {
        $this->interfaces = $interfaces;
    }

    /**
     * @return mixed
     */
    public function getInjectedDependencies()
    {
        return $this->injectedDependencies;
    }

    /**
     * @param mixed $injectedDependencies
     */
    public function setInjectedDependencies($injectedDependencies)
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
    public function setClassesInstances($classesInstances)
    {
        $this->classesInstances = $classesInstances;
    }

    /**
     * @return null
     */
    public function getRootNamespace()
    {
        return $this->rootNamespace;
    }

    /**
     * @param null $rootNamespace
     */
    public function setRootNamespace($rootNamespace)
    {
        $this->rootNamespace = $rootNamespace;
    }
}