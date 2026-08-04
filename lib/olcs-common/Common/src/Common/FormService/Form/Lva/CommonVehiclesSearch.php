<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;

/**
 * Common Vehicles Search Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommonVehiclesSearch
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    /**
     * Get form
     *
     * @return \Laminas\Form\FormInterface
     */
    public function getForm()
    {
        return $this->formHelper->createForm('Lva\VehicleSearch', false);
    }
}
