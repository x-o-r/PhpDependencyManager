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

    /**
     * @param $fileToParse
     * @return array
     * @throws Exception
     */
    public function parse($fileToParse)
    {
        try{
            $visitor = new Visitors\FileVisitor();
            $this->traverser->addVisitor($visitor);
            $stmts = $this->parser->parse(file_get_contents($fileToParse));
            $this->traverser->traverse($stmts);
            $this->traverser->removeVisitor($visitor);
            $DTOCollection = $visitor->getDTO();

            foreach ($DTOCollection as $DTO) // @TODO : find a way to set thoses values directly into the visitor
            {
                $DTO->setUses($visitor->getUses());
                $DTO->setNamespace($visitor->getNamespace());
                $DTO->setRootNamespace($visitor->getRootNamespace());
            }

            return $DTOCollection;
        } catch (Exception $e)
        {
           Throw $e;
        }
    }
}


