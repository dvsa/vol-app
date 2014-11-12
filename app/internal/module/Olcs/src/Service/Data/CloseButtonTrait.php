<?php

/**
 * Close Button Trait
 */
namespace Olcs\Service\Data;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Close Button Trait
 */
trait CloseButtonTrait
{

    protected $entityName;

    public function getCloseButton($id)
    {
        $entity = $this->getData($id);

        return [
            'label' => 'Close ' . $this->getEntityName(),
            'route' => $this->getEntityName(),
            'params' => [
                'case' => $entity['case']['id'],
                'action' => 'close',
                $this->getEntityName() => $entity['id']
            ]
        ];
    }

    public function getReopenButton($id)
    {
        $entity = $this->getData($id);

        return [
            'label' => 'Reopen ' . $this->getEntityName(),
            'route' => $this->getEntityName(),
            'params' => [
                'case' => $entity['case']['id'],
                'action' => 'reopen',
                $this->getEntityName() => $entity['id']
            ]
        ];
    }

    /**
     * Get Entity name
     * @return string
     */
    public function getEntityName()
    {
        if (empty($this->entityName)) {
            $this->setEntityName(strtolower($this->getServiceName()));
        }
        return $this->entityName;
    }

    /**
     * Set entityName
     * @param string $entityName
     * @return $this
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
        return $this;
    }
}
