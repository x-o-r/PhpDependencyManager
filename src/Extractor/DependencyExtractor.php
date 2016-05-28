<?php
namespace PhpDependencyManager\Extractor;

use PhpDependencyManager\FileParser;
use Hal\Component\File\Finder;
use Exception;

class DependencyExtractor
{
    private $DTOCollection = array();

    public function getDTOCollection() {
        return $this->DTOCollection;
    }

    public function getComponentsDTOArray() {
        return $this->componentsDTOArray;
    }

    public function analyseObjectDependencies($path) {
        $finder = new Finder();
        foreach ($finder->find($path) as $file)
        {
            // @TODO : Check syntax first
            $parser = new FileParser\PhpParser();
            try {
                foreach($parser->parse($file) as $object)
                {
                    $this->objectDTOArray[$object->getNamespace() . '\\' . $object->getName()] = $object;
                }
            } catch (Exception $e)
            {
                // @TODO : log
                continue;
            }
        }
    }

    public function analyseComponentsDependencies($composerJsonFile, $path) {
        $parser = new FileParser\ComposerJsonParser();
        $root_component = $parser->parse($composerJsonFile);
        array_push($this->DTOCollection, $root_component);

        $finder = new Finder('json'); // @TODO : switch from Hal\Component\File\Finder to something that allows to specify a filename
        foreach ($finder->find($path) as $file)
        {
            if (preg_match('/composer\.json/', $file)) {
                array_push($this->DTOCollection, $parser->parse($file));
            }
        }
    }
}