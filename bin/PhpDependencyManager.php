<?php

namespace PhpDependencyManager\Bin;

use PhpDependencyManager\Datamanager\DataManagerFactory;
use PhpDependencyManager\Extractor\DependencyExtractor;
use PhpDependencyManager\FileParser\ComposerJsonParserException;
use PhpDependencyManager\DataManager\DataManagerException;

require __DIR__ . '/../vendor/autoload.php';

if (empty($argv[1])) {
    exit;
}

try
{
    $dependencyExtractor = new DependencyExtractor();

    echo "+ Recusivly parse PHP files of " . $argv[1] . "\n";
    $dependencyExtractor->analyseObjectDependencies($argv[1]);

    if (!empty($argv[2])){
        if (file_exists($argv[2])){
            echo "+ Recusivly parse composer.json " . $argv[1] . "\n";
            $dependencyExtractor->analyseComponentsDependencies($argv[2]);
        }
    }

    $dataManager = DataManagerFactory::getInstance();

    echo "+ Drop previsous schema \n";
    $dataManager->dropSchema();

    echo "+ Create schema (see bin/query.log)\n";
    $dataManager->createSchema($dependencyExtractor->getObjectDTOArray(), $dependencyExtractor->getComponentsDTOArray());
    file_put_contents(__DIR__. '/query.log', $dataManager->dumpObjectAndRelation());

    echo "+ Sending query \n";
    $dataManager->sendQuery();
} catch (ComposerJsonParserException $e){
    echo ($e);
} catch (DataManagerException$e)
{
    echo("Database connection failed with following exceptions : \n$e");
} catch (Exception $e)
{
}
