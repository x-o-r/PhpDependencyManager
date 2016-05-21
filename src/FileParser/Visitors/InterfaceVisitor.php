<?php

namespace PhpDependencyManager\FileParser\Visitors;
use PhpDependencyManager\DTO\InterfaceDTO;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class InterfaceVisitor extends NodeVisitorAbstract implements NodeDataExchangeInterface
{
    private $interfaceDTO = null;

    public function __construct(InterfaceDTO $interfaceDTO)
    {
        $this->interfaceDTO = $interfaceDTO;
    }

    public function leaveNode(Node $node)
    {
        //
    }

    public function getDTO()
    {
        return $this->interfaceDTO;
    }
}