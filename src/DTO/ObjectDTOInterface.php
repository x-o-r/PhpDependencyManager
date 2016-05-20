<?php

namespace PhpDependencyManager\DTO;

interface ObjectDTOInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param $string
     */
    public function setName($string);
}