<?php

namespace PhpDependencyManager\Entities;
use PhpDependencyManager\DTO\ClassDTO;
use PhpDependencyManager\DTO\InterfaceDTO;
use PhpDependencyManager\DTO\ComponentDTO;
use PhpDependencyManager\GraphDatabaseManager\GraphDatabaseManagerException;
use PhpDependencyManager\GraphDatabaseManager\NodeManagerAbstract;
use Exception;

class MapEntities
{
    private $nodeManager;
    private $DTONameToClassmap = array(
        'PhpDependencyManager\DTO\ClassDTO' => 'class',
        'PhpDependencyManager\DTO\InterfaceDTO' => 'interface',
        'PhpDependencyManager\DTO\ComponentDTO' => 'component'
    );

    private $entities = array();
    private $objects = array();
    private $namespaces = array();
    private $components = array();
    private $fullComponentCollection = array();
    private $componentNamespaceCollection = array();
    private $rootNamespaceCollection = array();
    private $undiscoveredObject = array();

    public function __construct(NodeManagerAbstract $nodeManager) {
        $this->nodeManager = $nodeManager;
    }

    public function mapEntities(array $entities) {

        $this->entities = $entities;

        // 1 - create all entities
        $this->dispatchEntities();

        // 2 - create undiscovered entities
        foreach($this->objects as $objectID => $object) {
            try {
                $this->createNamespaces($object->getNamespace()); // Create namespaces nodes and relations for this object
                $this->addRelationHelper($objectID, $object->getNamespace(), "HAS_NS"); // Link object to his namespace
                $this->rootNamespaceCollection[$object->getRootNamespace()] = null;
                $this->handleInjectedDependencies($object);
                $this->handleExtend($object);
                $this->handleObjectInstanciation($object);
                $this->handleInterfaceImplementation($object);
            } catch (Exception $e) {
                // @TODO log
                continue;
            }
        }

        foreach ($this->components as $component) {
            $this->fullComponentCollection[$component->getName()] = $component;

            foreach ($component->getNamespaces() as $rootNamespace) {
                $this->componentNamespaceCollection[$rootNamespace] = $component->getID();
            }
        }

        $this->linkNamespacesToComponents();
        $this->linkComponentsToComponents();
    }

    private function handleInjectedDependencies($object) {
        foreach($object->getInjectedDependencies() as $injectedDependency) {
            $this->handleUndiscoveredEntities($injectedDependency, $object, "AGGREGATE");
        }
    }

    private function handleExtend($object) {
        if (!empty($object->getExtend())) {
            $this->handleUndiscoveredEntities($object->getExtend(), $object, "EXTEND");
        }
    }

    private function handleObjectInstanciation($object) {
        if ($object instanceof ClassDTO) {
            foreach($object->getClassesInstances() as $instance) {
                $this->handleUndiscoveredEntities($instance, $object, "COMPOSE");
            }
        }
    }

    private function handleInterfaceImplementation($object) {
        if ($object instanceof ClassDTO) {
            foreach($object->getInterfaces() as $interface) {
                $this->handleUndiscoveredEntities($interface, $object, "IMPLEMENT");
            }
        }
    }

    private function dispatchEntities()
    {
        foreach($this->entities as $id => $entity) {
            try {
                if ($entity instanceof ClassDTO || $entity instanceof InterfaceDTO) {
                    $this->objects[$id] = $entity;
                    $this->nodeManager->addNode(
                        $entity->getID(),
                        array('name' => $entity->getName()),
                        array($this->DTONameToClassmap[get_class($entity)], 'object')
                    );
                }
                if ($entity instanceof ComponentDTO) {
                    $this->components[$id] = $entity;
                    $this->nodeManager->addNode($entity->getID(), array('name' => $entity->getName()), array('component'));
                }
            } catch (GraphDatabaseManagerException $e) {
                Throw new \Exception($e);
            }
        }
    }

    private function handleUndiscoveredEntities($objectName, $contextObject, $relationType) {

        $objectIdentifier = $contextObject->getID();
        $objectNameExploded = explode('\\', $objectName);
        $objectNameContainsNamespace = preg_match('/\\\\/', $objectName); // Check if class name has a namespace

        if ($objectNameContainsNamespace) { // Class name contains namespace and exists in $this->objects

            if (array_key_exists($objectName, $this->objects)) { // Full namespace is specified in new
                $fullInstanciatedname = $this->objects[$objectName]->getID();
                $this->addRelationHelper($objectIdentifier, $fullInstanciatedname, $relationType);
                return;
            }
            if (array_key_exists($contextObject->getNamespace() . '\\'. $objectName, $this->objects)) { // Object namespace + full instanciated name
                $srcObject = $this->objects[$contextObject->getNamespace() . '\\'. $objectName];
                $fullInstanciatedName = $srcObject->getNamespace() . '\\'. $srcObject->getname();
                $this->addRelationHelper($objectIdentifier, $fullInstanciatedName, $relationType);
                return;
            }
            foreach ($contextObject->getUses() as $use) { // Analyse Uses
                if (array_key_exists($use.'\\'.$objectName, $this->objects)){ // Current Use + $objectName
                    $srcObject = $this->objects[$use.'\\'.$objectName];
                    $fullInstanciatedName = $srcObject->getNamespace() . '\\'. $srcObject->getname();
                    $this->addRelationHelper($objectIdentifier, $fullInstanciatedName, $relationType);
                    return;
                }
                $useParts = explode('\\', $use);
                if ($useParts[count($useParts)-1] == $objectNameExploded[0]){ // Final part of current use and first part of $objectName
                    $fullParts = implode('\\', array_unique(array_merge($useParts, $objectNameExploded)));
                    if (array_key_exists($fullParts, $this->objects)){
                        $srcObject = $this->objects[$fullParts];
                        $fullInstanciatedName = $srcObject->getNamespace() . '\\'. $srcObject->getname();
                        $this->addRelationHelper($objectIdentifier, $fullInstanciatedName, $relationType);
                        return;
                    }
                }
            }
        }
        if (array_key_exists($contextObject->getNamespace() . '\\' . $objectName, $this->objects)) { // Class name exists in current object's namespace
            $this->addRelationHelper($objectIdentifier, $contextObject->getNamespace() . '\\' . $objectName, $relationType);
            return;
        }

        // Check uses and aliases
        $uses = $contextObject->getUses();
        if (array_key_exists($objectName, $uses)) {
            if (array_key_exists($uses[$objectName], $this->objects)) {
                $existingObject = $this->objects[$uses[$objectName]];
                $this->addRelationHelper($objectIdentifier, $existingObject->getID(), $relationType);
                return;
            }
        }

        // Finally, $objectName was not discovered, create it as undiscovered class
        if (!in_array($objectName, $this->undiscoveredObject)) {
            $properties = array('name' => $objectName);
            if ($objectNameContainsNamespace) {        // If $object contains a namespace, add it to his attributes list

                $namespace = $objectNameExploded[0];
                for ($i = 1; $i < count($objectNameExploded) - 1; $i++) {
                    $namespace .= $objectNameExploded[$i];
                }
                $objectName = end($objectNameExploded);
                $properties['namespace'] = $namespace;
            }

            $this->nodeManager->addNode($objectName, $properties, array('undiscovered_object', 'object'));

            array_push($this->undiscoveredObject, $objectName);
        }
        $this->addRelationHelper($contextObject->getID(), $objectName, $relationType);
    }

    private function createNamespaces($namespace) {
        if (!empty($namespace)) {
            if (!array_key_exists($namespace, $this->namespaces)) {
                $namespaceParts = explode('\\', $namespace);
                $fullNamespace = null;

                foreach ($namespaceParts as $part) {
                    if (empty($fullNamespace)){
                        $fullNamespace = $part;
                        $this->createNamespaceHelper($fullNamespace, array('name' => $part), array("namespace"));
                    } else{
                        $nodeName = $fullNamespace . '\\' . $part;
                        $this->createNamespaceHelper($nodeName, array('name' => $part), array("namespace"));
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

    private function createNamespaceHelper($namespace, array $properties, array $labels) {
        if (!array_key_exists($namespace, $this->namespaces)) {
            $this->nodeManager->addNode($namespace, $properties, $labels);
        }
    }

    private function addRelationHelper($from, $to, $relationType){
        if ($relationType === "AGGREGATE"){
            $this->nodeManager->addRelation(
                $this->nodeManager->getNode($to),
                $this->nodeManager->getNode($from),
                $relationType
            );
        } else {
            $this->nodeManager->addRelation(
                $this->nodeManager->getNode($from),
                $this->nodeManager->getNode($to),
                $relationType
            );
        }
    }

    private function linkNamespacesToComponents() {
        if ($this->fullComponentCollection !== null) {
            foreach (array_keys($this->rootNamespaceCollection) as $rootNamespace) {
                if (array_key_exists($rootNamespace, $this->componentNamespaceCollection)) {
                    $componentName = $this->componentNamespaceCollection[$rootNamespace];

                    $this->nodeManager->addRelation(
                        $this->nodeManager->getNode($rootNamespace),
                        $this->nodeManager->getNode($componentName),
                        "DECLARED_IN_PKG"
                    );
                }
            }
        }
    }

    private function linkComponentsToComponents() {
        foreach ($this->components as $component) {
            foreach ($component->getRequires() as $requireName => $version) {
                if (array_key_exists($requireName, $this->fullComponentCollection)){
                    $this->nodeManager->addRelation(
                        $this->nodeManager->getNode($component->getName()),
                        $this->nodeManager->getNode($this->fullComponentCollection[$requireName]->getName()),
                        "REQUIRE"
                    );
                }
            }
        }
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
}