<?php

namespace Olcs\Controller\Traits;

use Dvsa\Olcs\Transfer\Query\Bus\BusReg as BusRegDto;

/**
 * Bus Controller Trait
 */
trait BusControllerTrait
{
    /**
     * Get the bus reg by id
     *
     * @param integer $id Id
     *
     * @return array
     */
    public function getBusReg($id = null)
    {
        if ($id === null) {
            $id = $this->params()->fromRoute('busRegId');
        }

        return $this->handleQuery(BusRegDto::create(['id' => $id]))->getResult();
    }
}
