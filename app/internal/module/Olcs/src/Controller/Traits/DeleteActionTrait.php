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
        $id = $this->params()->fromRoute('id');
        $this->makeRestCall($this->getDeleteServiceName(), 'DELETE', ['id' => $id]);
        $this->redirectToIndex();
    }

    /**
     * Should return the name of the service to call for deleting the item
     *
     * @return string
     */
    abstract public function getDeleteServiceName();
}
