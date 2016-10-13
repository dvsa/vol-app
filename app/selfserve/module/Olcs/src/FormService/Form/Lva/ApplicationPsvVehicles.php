<?php

namespace Olcs\FormService\Form\Lva;

use Zend\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application PSV vehicles
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationPsvVehicles extends PsvVehicles
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
