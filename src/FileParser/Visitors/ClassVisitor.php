<?php

namespace PhpDependencyManager\FileParser\Visitors;
use PhpDependencyManager\DTO\ClassDTO;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ClassVisitor extends NodeVisitorAbstract implements NodeDataExchangeInterface
{
    private $classDTO = null;
    private $injectedDependencies = array();

    public function __construct(ClassDTO $classDTO)
    {
        $this->classDTO = $classDTO;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\New_) {
            if ($node->class instanceof Node\Name) {
                $DTOclassesInstances = $this->classDTO->getClassesInstances();
                array_push($DTOclassesInstances,implode('\\', $node->class->parts));
                $this->classDTO->setClassesInstances($DTOclassesInstances);
            } else {
                //@TODO : log dynamic instanciation
            }
        }

        if ($node instanceof Node\Stmt\ClassMethod) {
            foreach ($node->params as $param)
            {
                if (!empty($param->name) && !empty($param->type) && !empty($param->type->parts)) {
                    $this->injectedDependencies[$param->name] = $param->type->parts[0];
                }
            }
        }
    }

    /**
     * @return ClassDTO
     */
    public function getDTO()
    {
        return $this->classDTO;
    }

    /**
     * @return array
     */
    public function getInjectedDependencies()
    {
        return $this->injectedDependencies;
    }
}