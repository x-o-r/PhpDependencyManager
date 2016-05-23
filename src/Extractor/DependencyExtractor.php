<?php
namespace PhpDependencyManager\Extractor;

use PhpDependencyManager\FileParser;
use Hal\Component\File\Finder;
use Exception;

class DependencyExtractor
{
    private $objectDTOArray = array();
    private $componentsDTOArray = array();

    public function getObjectDTOArray() {
        return $this->objectDTOArray;
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

    public function analyseComponentsDependencies($composerJsonFile) {
        $parser = new FileParser\ComposerJsonParser();
        $root_component = $parser->parse($composerJsonFile);
        array_push($this->componentsDTOArray, $root_component);

        $finder = new Finder('json'); // @TODO : switch from Hal\Component\File\Finder to something that allows to specify a filename
        foreach ($finder->find(dirname($composerJsonFile)) as $file)
        {
            if (preg_match('/composer\.json/', $file) && basename($file) !== basename($composerJsonFile)) {
                array_push($this->componentsDTOArray, $parser->parse($file));
            }
        }
    }
}