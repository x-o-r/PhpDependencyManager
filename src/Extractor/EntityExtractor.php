<?php
namespace PhpDependencyManager\Extractor;

use PhpDependencyManager\FileParser\PhpParser;
use PhpDependencyManager\FileParser\ComposerJsonParser;
use Hal\Component\File\Finder;
use Exception;

class EntityExtractor
{
    private $DTOCollection = array();

    public function getDTOCollection() {
        return $this->DTOCollection;
    }

    public function extractObject($path) {
        $finder = new Finder();
        foreach ($finder->find($path) as $file)
        {
            // @TODO : Check syntax first
            $parser = new PhpParser();
            try {
                foreach ($parser->parse($file) as $object)
                {
                    $this->DTOCollection[$object->getID()] = $object;
                }
            } catch (Exception $e)
            {
                // @TODO : log
                continue;
            }
        }
    }

    public function extractComponent($path, $rootComposerJsonFile = null) {

        $parser = new ComposerJsonParser();
        $finder = new Finder('json'); // @TODO : switch from Hal\Component\File\Finder to something that allows to specify a filename
        $filesToParse = $finder->find($path);
        if ($rootComposerJsonFile !== null && file_exists($rootComposerJsonFile)) {
            array_push($filesToParse, $rootComposerJsonFile);
        }

        foreach ($filesToParse as $file)
        {
            if (preg_match('/composer\.json/', $file)) {
                array_push($this->DTOCollection, $parser->parse($file));
            }
        }
    }
}