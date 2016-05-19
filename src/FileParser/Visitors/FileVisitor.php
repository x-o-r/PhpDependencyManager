<?php

namespace PhpDependencyManager\FileParser\Visitors;

use PhpDependencyManager\ClassDTO;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;

class FileVisitor extends NodeVisitorAbstract
{
    private $classDTOCollection = array();
    private $namespace = null;

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_)
        {
            $this->namespace = $node->name->parts[0];
        }

        if ($node instanceof Node\Stmt\Interface_) {
            $classDTO = new ClassDTO();
            $classDTO->type = "interface";
            $traverser = new NodeTraverser();
            $classDTO->classname    = $node->name;

            if (!empty($node->extends) && count($node->extends[0]->parts[0]))
                $classDTO->extend       = $node->extends[0]->parts[0];

            $visitor = new ClassVisitor($classDTO);
            $traverser->addVisitor($visitor);
            $traverser->traverse([$node]);
            $visitor = new InterfaceVisitor($classDTO);
            $classDTO = $visitor->getClassDTO();

            array_push($this->classDTOCollection, $classDTO);
        }


        if ($node instanceof Node\Stmt\Class_) {
            $classDTO = new ClassDTO();
            $classDTO->type = "class";
            $traverser = new NodeTraverser();

            $classDTO->classname    = $node->name;

            if (count($node->implements))
                $classDTO->interfaces   = $node->implements[0]->parts;

            if (!empty($node->extends) && count($node->extends->parts))
                $classDTO->extend       = $node->extends->parts[0];

            $visitor = new ClassVisitor($classDTO);
            $traverser->addVisitor($visitor);
            $traverser->traverse([$node]);
            $classDTO = $visitor->getClassDTO();
            $classDTO->injectedDependencies = $visitor->getInjectedDependencies();

            array_push($this->classDTOCollection, $classDTO);
        }
    }

    public function getClassDTOCollection()
    {
        return $this->classDTOCollection;
    }


    public function getNamespace()
    {
        return $this->namespace;
    }
}