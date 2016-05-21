<?php

namespace PhpDependencyManager\DTO;
use PhpDependencyManager\StringFilter\StringFilter;

class ComponentDTO implements ObjectDTOInterface
{
    private $name = null;
    private $namespaces = array();
    private $requires = array();

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null $name
     */
    public function setName($name)
    {
        $this->name = StringFilter::unifyObjectName($name);
    }

    /**
     * @return mixed
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * @param array $namespaces
     */
    public function setNamespaces(array $namespaces)
    {
        foreach($namespaces as $namespace){
            array_push($this->namespaces, rtrim(StringFilter::unifyObjectName($namespace), '_'));
        }
    }

    /**
     * @return array
     */
    public function getRequires()
    {
        return $this->requires;
    }

    /**
     * @param array $requires
     */
    public function setRequires($requires)
    {
        $this->requires = $requires;
    }
}