<?php

namespace Olcs\Controller\IrhpPermits;

/**
 * Irhp Fees redirect trait
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
trait IrhpFeesTrait
{
    /**
     * default action for the two IRHP fees routes
     *
     * @return mixed
     */
    public function dashRedirectAction()
    {
        return $this->redirect()->toRoute(
            'licence/permits',
            ['licence' => $this->getFromRoute('licence')]
        );
    }
}
