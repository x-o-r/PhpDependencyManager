<?php

namespace PhpDependencyManager\DTO;

class InterfaceDTO implements ObjectDTOInterface
{
    private $interfaceName = null;
    private $namespace = null;
    private $rootNamespace = null;

    private $extend = null;
    private $injectedDependencies = array();
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
        return $this->interfaceName;
    }

    /**
     * @param mixed $interfaceName
     */
    public function setName($interfaceName)
    {
//        $this->interfaceName = StringFilter::removeChars($interfaceName, StringFilter::INVALID_SYMS);
        $this->interfaceName = $interfaceName;
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
     * @return array
     */
    public function getInjectedDependencies()
    {
        return $this->injectedDependencies;
    }

    /**
     * @param array $injectedDependencies
     */
    public function setInjectedDependencies($injectedDependencies)
    {
        $this->injectedDependencies = $injectedDependencies;
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