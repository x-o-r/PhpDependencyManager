<?php

namespace PhpDependencyManager\Entities;
use PhpDependencyManager\DTO\ClassDTO;
use PhpDependencyManager\DTO\InterfaceDTO;
use PhpDependencyManager\DTO\ComponentDTO;
use PhpDependencyManager\GraphDatabaseManager\Neo4JNodeManager;

class MapEntities
{
    private $nodeManager;
    private $DTONameToClassmap = array(
        'PhpDependencyManager\DTO\ClassDTO' => 'class',
        'PhpDependencyManager\DTO\InterfaceDTO' => 'interface',
        'PhpDependencyManager\DTO\ComponentDTO' => 'component'
    );

    private $objects = array();
    private $namespaces = array();
    private $interfaces = array();
    private $components = array();
    private $componentsNamespaces = array();

    public function __construct(Neo4JNodeManager $nodeManager) {
        $this->nodeManager = $nodeManager;
        $nodeManager->deleteAllData();
    }

    public function dispatchEntities(array $entities)
    {
        foreach($entities as $id => $entity) {
            if ($entity instanceof ClassDTO || $entity instanceof InterfaceDTO) {
                $this->objects[$id] = $entity;
                $this->nodeManager->addNode(
                    $entity->getNamespace(),
                    array('name' => $entity->getName()),
                    array($this->DTONameToClassmap[get_class($entity)], 'object')
                );
                $this->createNamespaces($entity->getNamespace());
            }
            if ($entity instanceof ComponentDTO) {
                $this->components[$id] = $entity;
                $this->nodeManager->addNode($entity->getName(), array('name' => $entity->getName()), array('component'));
            }
        }

        $this->linkNamespacesToComponents();
        $this->linkComponentsToComponents();
        $this->linkUndiscoveredEntities();
    }

    private function createNamespaces($namespace) {
        if (!empty($namespace)) {
            if (!array_key_exists($namespace, $this->namespaces)) {
                $namespaceParts = explode('\\', $namespace);
                $fullNamespace = null;
                $previousNamespace = null;
                foreach ($namespaceParts as $part) {
                    if (empty($fullNamespace)){
                        $fullNamespace = $part;
                        $this->nodeManager->addNode($fullNamespace, array('name' => $part), array("namespace"));
                    } else{
                        $nodeName = $fullNamespace . '\\' . $part;
                        $this->nodeManager->addNode($nodeName, array('name' => $part), array("namespace"));
                        $this->nodeManager->addRelation(
                            $this->nodeManager->getNode($nodeName),
                            $this->nodeManager->getNode($fullNamespace),
                            "IS_IN_NS"
                        );
                        $fullNamespace = $nodeName;
                    }
                }
                $this->namespaces[$namespace] = null;
            }
        }
    }

    private function linkNamespacesToComponents() {
    }

    private function linkComponentsToComponents() {
    }

    private function linkUndiscoveredEntities() {
    }



    /**
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param array $entities
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;
    }

    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * @param array $namespaces
     */
    public function setNamespaces($namespaces)
    {
        $this->namespaces = $namespaces;
    }

    /**
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @param array $classes
     */
    public function setClasses($classes)
    {
        $this->classes = $classes;
    }

    /**
     * @return array
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * @param array $interfaces
     */
    public function setInterfaces($interfaces)
    {
        $this->interfaces = $interfaces;
    }

    /**
     * @return array
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @param array $components
     */
    public function setComponents($components)
    {
        $this->components = $components;
    }
}