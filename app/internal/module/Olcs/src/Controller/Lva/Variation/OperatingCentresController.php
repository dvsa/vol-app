<?php

/**
 * Internal Operating Centres Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Controller\Lva\Traits\VariationOperatingCentresControllerTrait;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Entity\ApplicationEntityService;

/**
 * Internal Operating Centres Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController implements
    ApplicationControllerInterface
{
    use VariationControllerTrait,
        VariationOperatingCentresControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';

    public function handleCrudAction(
        $data,
        $rowsNotRequired = ['add'],
        $childIdParamName = 'child_id',
        $route = null
    ) {
        unset($rowsNotRequired, $childIdParamName, $route);

        if ($data['action'] === 'Add schedule 4/1') {
            return $this->redirect()->toRouteAjax(
                'lva-variation/schedule41',
                array(
                    'application' => $this->getApplication()['id'],
                )
            );
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
