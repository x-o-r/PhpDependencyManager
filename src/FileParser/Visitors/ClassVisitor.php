<?php

namespace PhpDependencyManager\FileParser\Visitors;
use PhpDependencyManager\ClassDTO;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ClassVisitor extends NodeVisitorAbstract
{
    private $classDTO;
    private $injectedDependencies = array();
    public function __construct(ClassDTO $classDTO)
    {
        $this->classDTO = $classDTO;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Name) {
            $strippedClassName = explode('\\', $node->class->toString());
            $this->classDTO->classesInstances[] = end($strippedClassName);
        }

        if ($node instanceof Node\Stmt\ClassMethod) {
            foreach ($node->params as $param)
            {
                if (!empty($param->name) && !empty($param->type) && !empty($param->type->parts))
                    $this->injectedDependencies[$param->name] = $param->type->parts[0];
            }
        }
    }

    public function getClassDTO()
    {
        return $this->classDTO;
    }

    public function getInjectedDependencies()
    {
        return $this->injectedDependencies;
    }
}