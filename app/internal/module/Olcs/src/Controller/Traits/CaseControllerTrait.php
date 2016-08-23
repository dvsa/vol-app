<?php

namespace Olcs\Controller\Traits;

use Dvsa\Olcs\Transfer\Query\Cases\Cases;

/**
 * Case Controller Trait
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
trait CaseControllerTrait
{
    /**
     * Gets the case by id
     *
     * @param integer $id Id
     *
     * @return array
     */
    public function getCase($id = null)
    {
        if (is_null($id)) {
            $id = $this->params()->fromRoute('case');
        }

        $response = $this->handleQuery(Cases::create(['id' => $id]));

        return $response->getResult();
    }
}
