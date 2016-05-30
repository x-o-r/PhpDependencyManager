<?php

namespace PhpDependencyManager\Bin;

use Hoa\Iterator\Map;
use PhpDependencyManager\Entities\MapEntities;
use PhpDependencyManager\Extractor\EntityExtractor;
use PhpDependencyManager\Extractor\RelationExtractor;
use PhpDependencyManager\FileParser\ComposerJsonParserException;
use PhpDependencyManager\GraphDatabaseManager\Neo4JFactory;
use PhpDependencyManager\GraphDatabaseManager\Neo4JNodeManager;

require __DIR__ . '/../vendor/autoload.php';

if (empty($argv[1]) || !is_dir($argv[1])) {
    echo "usage : PhpDependencyManager $1 -> php sources directory $2{optional} -> path/to/root/composer.json\n";
    exit;
}

try {
    $entityExtractor = new EntityExtractor();

    echo "+ Recursivly parse PHP files in " . $argv[1] . "\n";
    $entityExtractor->extractObject($argv[1]);

    echo "+ Recursivly parse composer.json in " . $argv[1] . "\n";
    if (!empty($argv[2]) && file_exists($argv[2])) {
        $entityExtractor->extractComponent($argv[1], $argv[2]);
    } else {
        $entityExtractor->extractComponent($argv[1]);
    }

    echo "+ Creating nodes and relations\n";
    $neo4JClient = Neo4JFactory::getNeo4JClient(array('host'=>'localhost', 'port'=>'7474'));
    $neo4JNodeManager = new Neo4JNodeManager($neo4JClient);
    $neo4JNodeManager->deleteAllData();
    $mapEntities = new MapEntities($neo4JNodeManager);
    $mapEntities->mapEntities($entityExtractor->getDTOCollection());
} catch (ComposerJsonParserException $e){
    echo ($e . "\n");
} catch (DataBaseDriverException $e) {
    echo ($e . "\n");
} catch (Exception $e) {
    echo ($e . "\n");
}
