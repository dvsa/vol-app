<?php

/**
 * Internal Application Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Internal Application Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController
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
    protected function handleCrudAction($data, $rowsNotRequired = array('add'))
    {
        $response = $this->getAdapter()->checkTrafficAreaAfterCrudAction($data);

        if ($response !== null) {
            return $response;
        }

        return parent::handleCrudAction($data);
    }
}
