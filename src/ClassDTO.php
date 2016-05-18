<?php

namespace DependencyManager;

class ClassDTO
{
    public $classname, $namespace, $extend, $type = null;
    public $interfaces, $injectedDependencies, $classesInstances = array();
}