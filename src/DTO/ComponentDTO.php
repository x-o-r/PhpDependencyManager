<?php

namespace PhpDependencyManager\DTO;

class ComponentDTO implements ObjectDTOInterface
{
    private $name = null;
    private $namespaces, $requires = array();

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
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * @param mixed $namespaces
     */
    public function setNamespaces($namespaces)
    {
        $this->namespaces = $namespaces;
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