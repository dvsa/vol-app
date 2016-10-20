<?php

namespace Olcs\FormService\Form\Lva\TransportManager;

use Common\FormService\Form\Lva\AbstractLvaFormService;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\Form\Form;

/**
 * Application Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationTransportManager extends AbstractLvaFormService
{
    use ButtonsAlterations;

    /**
     * Get form
     *
     * @return Form
     */
    public function getForm()
    {
        $form = $this->getFormHelper()->createForm('Lva\TransportManagers');

        $this->alterForm($form);

        return $form;
    }

    /**
     * Alter form
     *
     * @param Form $form form
     *
     * @return Form
     */
    protected function alterForm($form)
    {
        $this->alterButtons($form);

        return $form;
    }
}
