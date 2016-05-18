<?php
namespace DependencyManager;

require __DIR__ . '/../vendor/autoload.php';

use Hal\Component\File\Finder;

class DependencyExtractor
{
    private $classesDTOArray = array();

    public function getClassesDTOArray()
    {
        return $this->classesDTOArray;
    }
    
    public function analyse()
    {
        $finder = new Finder();
        foreach ($finder->find(__DIR__ . "/../test") as $file)
        {
            $parser = new FileParser();
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

$dependencyExtractor = new DependencyExtractor();
$dependencyExtractor->analyse();

try
{
    $dataCreator = DataCreatorFactory::getInstance();
    $dataCreator->createSchema($dependencyExtractor->getClassesDTOArray());
} catch (Error $e)
{
    //
}
