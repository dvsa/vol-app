<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;

/**
 * Licence Goods Vehicles Filters  Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceGoodsVehiclesFilters extends CommonGoodsVehiclesFilters
{
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        parent::__construct($formHelper);
    }

    #[\Override]
    public function getForm()
    {
        $form = parent::getForm();

        $this->formHelper->remove($form, 'specified');

        return $form;
    }
}
