<?php

/**
 * Case Controller Trait
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Traits;

use Zend\Mvc\MvcEvent;

/**
 * Case Controller Trait
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
trait CaseControllerTrait
{
    /**
     * Gets the case by ID.
     *
     * @param integer $id
     * @return array
     */
    public function getCase($id = null)
    {
        if (is_null($id)) {
            $id = $this->params()->fromRoute('case');
        }

        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Olcs\Service\Data\Cases');
        return $service->fetchCaseData($id);
    }

    /**
     * Sets the table filters.
     *
     * @param mixed $filters
     */
    public function setTableFilters($filters)
    {
        $this->getViewHelperManager()->get('placeholder')->getContainer('tableFilters')->set($filters);
    }
}
