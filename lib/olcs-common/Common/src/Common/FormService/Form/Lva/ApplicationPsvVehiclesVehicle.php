<?php

namespace Common\FormService\Form\Lva;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;

class ApplicationPsvVehiclesVehicle extends AbstractPsvVehiclesVehicle
{
    public function __construct(protected FormHelperService $formHelper, protected FormServiceManager $formServiceLocator)
    {
    }

    /**
     * @return void
     */
    #[\Override]
    protected function alterForm($form, $params)
    {
        $dataFieldset = $form->get('licence-vehicle');
        $specifiedDate = $dataFieldset->get('specifiedDate');
        $this->formHelper->disableDateElement($specifiedDate);
        $this->formHelper->disableDateElement($dataFieldset->get('removalDate'));

        $specifiedDate->getYearElement()->setValue('');
        $specifiedDate->getMonthElement()->setValue('');
        $specifiedDate->getDayElement()->setValue('');
        $specifiedDate->getHourElement()->setValue('');
        $specifiedDate->getMinuteElement()->setValue('');

        parent::alterForm($form, $params);

        if ($params['isRemoved']) {
            $form->get('form-actions')->remove('submit');
        }
    }
}
