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
     *
     * @NOTE: currently duped across internal and external as calls parent
     */
    protected function handleCrudAction(
        $data,
        $rowsNotRequired = ['add'],
        $childIdParamName = 'child_id',
        $route = null
    ) {
        if ($data['action'] === 'Add schedule 4/1') {
            return $this->redirect()->toRoute(
                'lva-application/schedule41',
                array(
                    'application' => $this->getIdentifier(),
                    'controller' => 'ApplicationSchedule41Controller',
                    'action' => 'index'
                )
            );
        }

        $response = $this->getAdapter()->checkTrafficAreaAfterCrudAction($data);

        if ($response !== null) {
            return $response;
        }

        return parent::handleCrudAction($data);
    }
}
