<?php

namespace PhpDependencyManager\DTO;

class ComponentDTO implements DTOInterface
{
    private $name;
    private $mainName;
    private $subName;
    private $namespaces = array();
    private $requires = array();

    /**
     * @return string
     * @throws DTOException
     */
    public function getName()
    {
        if ($this->name === null) {
            throw new DTOException(__CLASS__ . " : component name cannot be null");
        }
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        $parts = explode('/', $name);
        if (count($parts)>=2){
            $this->mainName = $parts[0];
            $this->subName = $parts[1];
        }
    }

    /**
     * @return string|null
     */
    public function getMainName()
    {
        if (empty($this->mainName)){
            return $this->getName();
        }
        return $this->mainName;
    }

    /**
     * @return string|null
     */
    public function getSubName()
    {
        if (empty($this->subName)){
            return $this->getName();
        }
        return $this->subName;
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
            array_push($this->namespaces, rtrim($namespace, '\\'));
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
    public function setRequires(array $requires)
    {
        $this->requires = $requires;
    }
}