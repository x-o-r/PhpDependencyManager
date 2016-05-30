<?php

namespace PhpDependencyManager\DTO;

interface DTOInterface
{
    /**
     * @return string
     */
    public function getID();

    /**
     * @param string $id
     */
    public function setID($id);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);
}