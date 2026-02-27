<?php

declare(strict_types=1);

namespace Olcs\FormService\Form\Lva;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\FormService\Form\Lva\VehiclesDeclarationsPsvOperateLarge;

class VehiclesDeclarationsLarge extends VehiclesDeclarationsPsvOperateLarge
{
    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form
     * @return \Laminas\Form\Form
     */
    #[\Override]
    protected function alterForm($form)
    {
        parent::alterForm($form);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        /**
         * @todo https://dvsa.atlassian.net/browse/VOL-6487 override as GDS classes cause problems on internal pages
         * @var SingleCheckbox $checkbox
         */
        $checkbox = $form->get('psvNoSmallVhlConfirmation');
        $checkbox->setLabelAttributes(['class' => 'form-control form-control--checkbox form-control--advanced']);

        return $form;
    }
}
