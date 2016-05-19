<?php
namespace PhpDependencyManager;

use PhpDependencyManager\FileParser;
use Hal\Component\File\Finder;

class DependencyExtractor
{
    private $classesDTOArray = array();

    public function getClassesDTOArray()
    {
        return $this->classesDTOArray;
    }

    public function analyse($path)
    {
        $finder = new Finder();
        foreach ($finder->find($path) as $file)
        {
            $parser = new FileParser\PhpParser();
            try
            {
                $parsedClasses = $parser->parse($file);
            } catch (Error $e)
            {
                // @TODO : log
                continue;
            }

            $this->classesDTOArray[basename($file)] = $parsedClasses;
        }
    }
}