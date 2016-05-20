<?php

namespace PhpDependencyManager\DTO;

class InterfaceDTO implements ObjectDTOInterface
{
    private $interfaceName, $namespace, $extend;
    private $injectedDependencies = array();

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


}