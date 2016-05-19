<?php

namespace PhpDependencyManager\FileParser\Visitors;
use PhpDependencyManager\ClassDTO;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class InterfaceVisitor extends NodeVisitorAbstract
{
    private $classDTO;

    public function __construct(ClassDTO $classDTO)
    {
        $this->classDTO = $classDTO;
    }

    public function leaveNode(Node $node)
    {
        //
    }

    public function getClassDTO()
    {
        return $this->classDTO;
    }
}