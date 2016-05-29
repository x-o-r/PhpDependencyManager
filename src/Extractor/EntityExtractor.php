<?php
namespace PhpDependencyManager\Extractor;

use PhpDependencyManager\FileParser\PhpParser;
use PhpDependencyManager\FileParser\ComposerJsonParser;
use Hal\Component\File\Finder;
use Exception;
use PhpDependencyManager\GraphDatabaseManager\Neo4JNodeManager;

class EntityExtractor
{
    private $DTOCollection = array();

    public function getDTOCollection() {
        return $this->DTOCollection;
    }

    public function getComponentsDTOArray() {
        return $this->componentsDTOArray;
    }

    public function extractObject($path) {
        $finder = new Finder();
        foreach ($finder->find($path) as $file)
        {
            // @TODO : Check syntax first
            $parser = new PhpParser();
            try {
                foreach($parser->parse($file) as $object)
                {
                    $this->DTOCollection[$object->getNamespace() . '\\' . $object->getName()] = $object;
                }
            } catch (Exception $e)
            {
                // @TODO : log
                continue;
            }
        }
    }

    public function extractComponent($path) {
        $parser = new ComposerJsonParser();
        $finder = new Finder('json'); // @TODO : switch from Hal\Component\File\Finder to something that allows to specify a filename
        foreach ($finder->find($path) as $file)
        {
            if (preg_match('/composer\.json/', $file)) {
                array_push($this->DTOCollection, $parser->parse($file));
            }
        }
    }
}