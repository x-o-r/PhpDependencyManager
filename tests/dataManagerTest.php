<?php

use PhpDependencyManager\Datamanager\DataManager;
use PhpDependencyManager\Extractor\DependencyExtractor;

class dataManagerTest extends PHPUnit_Framework_TestCase
{
    public function testCreateSchema()
    {
        $queryResult = file_get_contents(__DIR__ . "/query.log");
        $dependencyExtractor = new DependencyExtractor();
        $dependencyExtractor->analyseObjectDependencies(__DIR__ . "/codeSamples/");
        $dependencyExtractor->analyseComponentsDependencies(__DIR__ . "/codeSamples/1/composer.json", __DIR__ . "/codeSamples/");
        $dataManager = new DataManager();
        $dataManager->createSchema($dependencyExtractor->getObjectDTOArray(), $dependencyExtractor->getComponentsDTOArray());
        $dataManagerQuery = implode(array_merge($dataManager->nodeQueryCollection, $dataManager->realComponentQueryCollection, $dataManager->relationQueryCollection));
        $this->assertEquals($queryResult, $dataManagerQuery);
    }
}