<?php
namespace PhpDependencyManager\FileParser;
use Exception;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

class PhpParser
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
            $visitor = new Visitors\FileVisitor();
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

        } catch (Exception $e)
        {
           Throw $e;
        }
    }
}


