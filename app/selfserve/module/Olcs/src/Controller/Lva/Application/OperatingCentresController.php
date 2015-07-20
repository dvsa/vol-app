<?php

/**
 * External Application Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';

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
        $response = $this->getAdapter()->checkTrafficAreaAfterCrudAction($data);

        if ($response !== null) {
            return $response;
        }

        return parent::handleCrudAction($data);
    }

    public function alterForm($form)
    {
        $form = parent::alterForm($form);

        $form->get('table')->get('table')->getTable()->removeAction('schedule41');

        return $form;
    }
}
