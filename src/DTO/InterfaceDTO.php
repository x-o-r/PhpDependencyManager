<?php

namespace PhpDependencyManager\DTO;

class InterfaceDTO implements DTOInterface
{
    private $interfaceName;
    private $namespace;
    private $rootNamespace;
    private $extend;

    private $uses = array();
    private $injectedDependencies = array();

    /**
     * @return string
     * @throws DTOException
     */
    public function getName()
    {
        if ($this->interfaceName === null) {
            throw new DTOException(__CLASS__ . " : interface name cannot be null");
        }
        return $this->interfaceName;
    }

    /**
     * @param mixed $interfaceName
     */
    public function setName($interfaceName)
    {
        $this->interfaceName = $interfaceName;
    }

    /**
     * @return mixed
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
     * @param mixed $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /***
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
}