<?php
namespace PhpDependencyManager;

use PhpDependencyManager\FileParser;
use Hal\Component\File\Finder;
use Exception;

class DependencyExtractor
{
    private $classesDTOArray = array();
    private $componentsDTOArray = array();

    public function getClassesDTOArray() {
        return $this->classesDTOArray;
    }

    public function getComponentsDTOArray() {
        return $this->componentsDTOArray;
    }

    public function analyseClassesDependencies($path) {
        $finder = new Finder();
        foreach ($finder->find($path) as $file)
        {
            $parser = new FileParser\PhpParser();
            try
            {
                $parsedClasses = $parser->parse($file);
            } catch (Exception $e)
            {
                // @TODO : log
                continue;
            }

            $this->classesDTOArray[basename($file)] = $parsedClasses;
        }
    }
    public function analyseComponentsDependencies($composerJsonFile) {
//        $this->$componentsDTOArray
    }
}