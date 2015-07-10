<?php

/**
 * Case Controller Trait
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Traits;

use Dvsa\Olcs\Transfer\Query\Cases\Cases;
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

        $response = $this->handleQuery(Cases::create(['id' => $id]));

        // @NOTE added for backwards compatibility until we know what we are doing with these objects
        return new \Olcs\Data\Object\Cases($response->getResult());
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
