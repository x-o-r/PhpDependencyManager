<?php
namespace DependencyManager;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class FileParser
{
    private $parser;
    private $traverser;

    public function __construct(){
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP5);
        $this->traverser = new NodeTraverser();
    }

    public function parse($fileToParse)
    {
        try{
            $visitor = new FileVisitor();
            $this->traverser->addVisitor($visitor);
            $stmts = $this->parser->parse(file_get_contents($fileToParse));
//            var_dump($stmts);

            $this->traverser->traverse($stmts);
            $this->traverser->removeVisitor($visitor);
            $classesCollection = $visitor->getClassDTOCollection();

            // Ajout du namespace
            foreach ($classesCollection as $classDTO)
            {
                $classDTO->namespace = $visitor->getNameSpace();
            }

            return $classesCollection;

        } catch (Error $e)
        {
           Throw new Exception($e);
        }
    }
}

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
        if ($node instanceof Node\Expr\New_ && method_exists($node->class, "toString")) { // @TODO : journaliser les instanciations dynamique "new $variable"
            $this->classDTO->classesInstances[] = $node->class->toString();
        }

        if ($node instanceof Node\Stmt\ClassMethod) {
            foreach ($node->params as $param)
            {
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

