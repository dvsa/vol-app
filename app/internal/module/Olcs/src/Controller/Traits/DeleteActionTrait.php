<?php

namespace Olcs\Controller\Traits;

/**
 * Class DeleteActionTrait
 * @package Olcs\Controller
 */
trait DeleteActionTrait
{
    /**
     * Performs a delete action and redirects to the index
     */
    public function deleteAction()
    {
        $identifierName = $this->getIdentifierName();
        $id = $this->params()->fromRoute($identifierName);
        $this->makeRestCall($this->getDeleteServiceName(), 'DELETE', ['id' => $id]);

        $this->addErrorMessage('Deleted sucessfully');

        $this->redirectToIndex();
    }

    /**
     * Should return the name of the service to call for deleting the item
     *
     * @return string
     */
    public function getDeleteServiceName()
    {
        if (method_exists($this, 'getService')) {
            return $this->getService();
        }

        throw \LogicExcpetion('getDeleteServiceName or getService methods were not implemented.');
    }

    /**
     * Retrieve the route match/query parameter name containing the identifier
     *
     * @return string
     */
    public function getIdentifierName()
    {
        if (property_exists($this, 'identifierName')) {
            return $this->identifierName;
        }

        return 'id';
    }
}
