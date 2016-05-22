<?php
namespace PhpDependencyManager\FileParser;
use Exception;
use PhpDependencyManager\DTO\ComponentDTO;

class ComposerJsonParser
{
    /**
     * @param $fileToParse
     * @return ComponentDTO
     * @throws Exception
     */
    public function parse($fileToParse)
    {
        try{
            $json = json_decode(file_get_contents($fileToParse), true);
            $componentDTO = new ComponentDTO();
            $componentDTO->setName($json['name']);
            if (array_key_exists("autoload", $json)){
                if (array_key_exists("psr-4", $json['autoload'])) {
                    $componentDTO->setNamespaces(array_keys($json['autoload']['psr-4']));
                } else if (array_key_exists("psr-0", $json['autoload'])) {
                    $componentDTO->setNamespaces(array_keys($json['autoload']['psr-0']));
                }
            } else{
                Throw new ComposerJsonParserException("Autoload key must be defined in " . $fileToParse);
            }
            if (array_key_exists("require", $json)){
                $componentDTO->setRequires($json['require']);
            } else{
                Throw new ComposerJsonParserException("Require key must be defined in " . $fileToParse);
            }
            return $componentDTO;
        } catch (Exception $e)
        {
           Throw $e;
        }
    }
}