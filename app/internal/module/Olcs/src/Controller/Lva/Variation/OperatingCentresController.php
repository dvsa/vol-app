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
}
