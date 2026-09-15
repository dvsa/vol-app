<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;

/**
 * Common PSV Vehicles Filter Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommonPsvVehiclesFilters
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    /**
     * Get Form
     *
     * @return \Laminas\Form\FormInterface
     */
    public function getForm()
    {
        return $this->alterForm($this->formHelper->createForm('Lva\PsvVehicleFilter', false));
    }

    /**
     * Form
     *
     * @param \Laminas\Form\FormInterface $form Form
     *
     * @return \Laminas\Form\FormInterface
     */
    protected function alterForm($form)
    {
        $this->formHelper->remove($form, 'vrm');
        $this->formHelper->remove($form, 'specified');
        $this->formHelper->remove($form, 'disc');
        $this->formHelper->remove($form, 'limit');

        return $form;
    }
}
