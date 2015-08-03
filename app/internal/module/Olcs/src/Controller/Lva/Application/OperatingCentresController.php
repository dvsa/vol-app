<?php

/**
 * Internal Application Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Internal Application Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController implements
    ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';

    /**
     * Override handle crud action to check we've got a traffic area
     * when adding more than one OC
     */
    protected function handleCrudAction(
        $data,
        $rowsNotRequired = ['add'],
        $childIdParamName = 'child_id',
        $route = null
    ) {
        unset($rowsNotRequired, $childIdParamName, $route);

        if ($data['action'] === 'Add schedule 4/1') {
            return $this->redirect()->toRouteAjax(
                'lva-application/schedule41',
                array(
                    'application' => $this->getIdentifier(),
                )
            );
        }

        $response = $this->getAdapter()->checkTrafficAreaAfterCrudAction($data);

        if ($response !== null) {
            return $response;
        }

        return parent::handleCrudAction($data);
    }

    protected function alterForm($form)
    {
        $application = $this->getApplication();

        if ($application['goodsOrPsv'] !== LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $form->get('table')->get('table')->getTable()->removeAction('schedule41');
            return $form;
        }

        if ($application['status']['id'] !== ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION) {
            $form->get('table')->get('table')->getTable()->removeAction('schedule41');
            return $form;
        }

        $schedule41 = $this->getServiceLocator()
            ->get('Entity\Schedule41')
            ->getByApplication($application['id']);
        if ($schedule41['Count'] > 0) {
            $form->get('table')->get('table')->getTable()->removeAction('schedule41');
        }

        return $form;
    }
}
