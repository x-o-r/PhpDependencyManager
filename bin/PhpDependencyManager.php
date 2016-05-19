<?php

use PhpDependencyManager\DependencyExtractor;
use PhpDependencyManager\DataManagerFactory;

require __DIR__ . '/../vendor/autoload.php';

if (empty($argv[1]))
  exit;

$dependencyExtractor = new DependencyExtractor();
$dependencyExtractor->analyseClassesDependencies($argv[1]);

if (!empty($argv[2])){
    if (file_exists($argv[2])){
        $dependencyExtractor->analyseComponentsDependencies($argv[2]);
    }
}

try
{
    $dataManager = DataManagerFactory::getInstance();
    $dataManager->dropSchema();
    $dataManager->createSchema($dependencyExtractor->getClassesDTOArray(), $dependencyExtractor->getComponentsDTOArray());
} catch (Exception $e)
{
    echo "Database connection failed\n";
}
