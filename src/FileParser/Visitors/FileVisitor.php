<?php

namespace PhpDependencyManager\FileParser\Visitors;
use PhpDependencyManager\DTO\ClassDTO;
use PhpDependencyManager\DTO\InterfaceDTO;
use PhpDependencyManager\StringFilter\StringFilter;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;

class FileVisitor extends NodeVisitorAbstract implements NodeDataExchangeInterface
{
    private $namespace = null;
    private $phpObjectDTOCollection = array();

    public function leaveNode(Node $node)
    {
        // Set file's namespace
        if ($node instanceof Node\Stmt\Namespace_)
        {
            if (!(empty($node->name->parts)) && is_array($node->name->parts))
            {
                $namespace = null;
                foreach ($node->name->parts as $namespacePart) {
                    if (empty($namespace)) {
                        $namespace = $namespacePart;
                    } else {
                        $namespace .= '_' . $namespacePart;
                    }
                }
                $this->namespace = StringFilter::unifyObjectName($namespace);
            }
        }

        if ($node instanceof Node\Stmt\Interface_) {
            $interfaceDTO = new InterfaceDTO();
            $traverser = new NodeTraverser();
            $interfaceDTO->setName(StringFilter::unifyObjectName($node->name));

            if (!empty($node->extends) && count($node->extends[0]->parts[0])) {
                $interfaceDTO->setExtend(StringFilter::unifyObjectName($node->extends[0]->parts[0]));
            }

            $visitor = new InterfaceVisitor($interfaceDTO);
            $traverser->addVisitor($visitor);
            $traverser->traverse([$node]);
            $interfaceDTO = $visitor->getDTO();

            array_push($this->phpObjectDTOCollection, $interfaceDTO);
        }

        if ($node instanceof Node\Stmt\Class_) {
            $classDTO = new ClassDTO();
            $traverser = new NodeTraverser();

            $classDTO->setName(StringFilter::unifyObjectName($node->name));

            if (count($node->implements)){
                $classDTO->setInterfaces($node->implements[0]->parts);
            }

            if (!empty($node->extends) && count($node->extends->parts)){
                $classDTO->setExtend($node->extends->parts[0]);
            }

            $visitor = new ClassVisitor($classDTO);
            $traverser->addVisitor($visitor);
            $traverser->traverse([$node]);
            $classDTO = $visitor->getDTO();
            $classDTO->setInjectedDependencies($visitor->getInjectedDependencies());

            array_push($this->phpObjectDTOCollection, $classDTO);
        }
    }

    public function getDTO()
    {
        return $this->phpObjectDTOCollection;
    }


    public function getNamespace()
    {
        return $this->namespace;
    }
}