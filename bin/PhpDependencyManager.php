<?php

use DependencyManager\DependencyExtractor;
use DependencyManager\DataCreatorFactory;

require __DIR__ . '/../vendor/autoload.php';


if (empty($argv[1])) {
    echo 'go fuck yourself';exit;die;
}

$dependencyExtractor = new DependencyExtractor();
$dependencyExtractor->analyse($argv[1]);

try
{
    var_dump($dependencyExtractor->getClassesDTOArray());exit;

    $dataCreator = DataCreatorFactory::getInstance();
    $dataCreator->createSchema();
} catch (Error $e)
{
    //
}
