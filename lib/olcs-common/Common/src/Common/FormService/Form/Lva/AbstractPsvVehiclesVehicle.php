<?php

namespace Common\FormService\Form\Lva;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Psv Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractPsvVehiclesVehicle
{
    protected FormHelperService $formHelper;

    protected FormServiceManager $formServiceLocator;

    public function getForm($request, $params): \Laminas\Form\FormInterface
    {
        $form = $this->formHelper->createFormWithRequest('Lva\PsvVehiclesVehicle', $request);

        $this->alterForm($form, $params);

        return $form;
    }

    /**
     * Generic form alterations
     *
     * @param \Laminas\Form\Form $form
     * @param array $params
     *
     * @return void
     */
    protected function alterForm($form, $params)
    {
        if ($params['mode'] == 'add' || $params['location'] == 'external') {
            $this->formHelper->remove($form, 'vehicle-history-table');
        }

        $this->formServiceLocator->get('lva-psv-vehicles-vehicle')->alterForm($form);

        $this->formHelper->remove($form, 'licence-vehicle->discNo');

        $this->formServiceLocator->get('lva-generic-vehicles-vehicle')->alterForm($form, $params);

        if ($params['isRemoved']) {
            $this->formHelper->disableElement($form, 'data->vrm');

            if ($form->get('data')->has('makeModel')) {
                $this->formHelper->disableElement($form, 'data->makeModel');
            }

            $this->formHelper->disableElements($form->get('licence-vehicle'));
        }
    }
}
