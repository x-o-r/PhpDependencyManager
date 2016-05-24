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
    private $rootNamespace = null;
    private $objectDTOCollection = array();
    private $uses = array();

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Useuse) {
            if (is_array($node->name->parts) && count($node->name->parts)) {
                $classTarget = end($node->name->parts);
                if ($classTarget === $node->alias) {
                    if(!array_key_exists($classTarget, $this->uses)) {
                        $this->uses[$classTarget] = implode('\\', $node->name->parts);
                    }
                } else if(!array_key_exists($node->alias, $this->uses)) {
                    $this->uses[$node->alias] = implode('\\', $node->name->parts);
                }
            }
        }

        if ($node instanceof Node\Stmt\Namespace_) {
            $partsCount = count($node->name->parts);
            if (is_array($node->name->parts) && $partsCount) {
                $namespace = $node->name->parts[0];
                $this->rootNamespace = $namespace;
                for ($i=1; $i<$partsCount; $i++){
                        $namespace .= '\\' . $node->name->parts[$i];
                }

            }
            $this->namespace = $namespace;
        }

        if ($node instanceof Node\Stmt\Interface_) {
            $interfaceDTO = new InterfaceDTO();
            $traverser = new NodeTraverser();
            $interfaceDTO->setName($node->name);

            if (!empty($node->extends) && count($node->extends[0]->parts[0])) {
                $interfaceDTO->setExtend($node->extends[0]->parts[0]);
            }

            $visitor = new InterfaceVisitor($interfaceDTO);
            $traverser->addVisitor($visitor);
            $traverser->traverse([$node]);
            $interfaceDTO = $visitor->getDTO();

            array_push($this->objectDTOCollection, $interfaceDTO);
        }

        if ($node instanceof Node\Stmt\Class_) {
            $classDTO = new ClassDTO();
            $traverser = new NodeTraverser();

            $classDTO->setName($node->name);

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

            array_push($this->objectDTOCollection, $classDTO);
        }
    }

    /**
     * @return null
     */
    public function getRootNamespace()
    {
        return $this->rootNamespace;
    }
    /**
     * @return array
     */
    public function getUses() {
        return $this->uses;
    }
    /**
     * @return array
     */
    public function getDTO() {
        return $this->objectDTOCollection;
    }
    /**
     * @return string
     */
    public function getNamespace() {
        return $this->namespace;
    }
}