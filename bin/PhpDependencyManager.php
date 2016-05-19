<?php

use PhpDependencyManager\DependencyExtractor;
use PhpDependencyManager\DataManagerFactory;

require __DIR__ . '/../vendor/autoload.php';

if (empty($argv[1])) {
  exit;
}

$dependencyExtractor = new DependencyExtractor();
$dependencyExtractor->analyse($argv[1]);
//var_dump($dependencyExtractor->getClassesDTOArray()); exit;

try
{
//    var_dump($dependencyExtractor->getClassesDTOArray());exit;
    $dataManager = DataManagerFactory::getInstance();
    $dataManager->dropSchema();
    $dataManager->createSchema($dependencyExtractor->getClassesDTOArray());
} catch (Error $e)
{
    //
}
