<?php

namespace Olcs\FormService\Form\Lva;

use Zend\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\FormService\Form\Lva\ApplicationGoodsVehicles as CommonGoodsVehicles;

/**
 * Application Goods vehicles
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationGoodsVehicles extends CommonGoodsVehicles
{
    use ButtonsAlterations;

    /**
     * Make form alterations
     *
     * @param Form $form form
     *
     * @return Form
     */
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $this->alterButtons($form);

        return $form;
    }
}
