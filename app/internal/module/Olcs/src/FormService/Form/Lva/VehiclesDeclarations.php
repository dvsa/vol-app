<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\VehiclesDeclarations as CommonVehiclesDeclarations;
use Common\Service\Helper\FormHelperService;

/**
 * Vehicles Declarations Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class VehiclesDeclarations extends CommonVehiclesDeclarations
{
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        parent::__construct($formHelper);
    }

    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form
     * @return \Laminas\Form\Form
     */
    protected function alterForm($form)
    {
        parent::alterForm($form);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
