<?php

namespace PhpDependencyManager\Bin;

use PhpDependencyManager\Datamanager\DataManagerFactory;
use PhpDependencyManager\Extractor\DependencyExtractor;

require __DIR__ . '/../vendor/autoload.php';

if (empty($argv[1])) {
    exit;
}

$dependencyExtractor = new DependencyExtractor();
$dependencyExtractor->analyseObjectDependencies($argv[1]);

if (!empty($argv[2])){
    if (file_exists($argv[2])){
        $dependencyExtractor->analyseComponentsDependencies($argv[2]);
    }
}

try
{
    $dataManager = DataManagerFactory::getInstance();
    $dataManager->dropSchema();
    $dataManager->createSchema($dependencyExtractor->getObjectDTOArray(), $dependencyExtractor->getComponentsDTOArray());
//    var_dump($dataManager->getQuery());exit;
    file_put_contents(__DIR__. '/query.log', $dataManager->getQuery());
    $dataManager->sendQuery();
} catch (Exception $e)
{
    echo "Database connection failed with following exceptions : \n$e";
}
