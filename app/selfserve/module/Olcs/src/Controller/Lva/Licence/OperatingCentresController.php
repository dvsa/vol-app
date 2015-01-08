<?php

/**
 * External Licencing Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * External Licencing Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController
{
    use LicenceControllerTrait,
        Lva\Traits\LicenceOperatingCentresControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';

    /**
     * Override add action to show variation warning
     */
    public function addAction()
    {
        $view = new ViewModel(
            array(
                'licence' => $this->getIdentifier()
            )
        );
        $view->setTemplate('licence/add-authorisation');

        return $this->render($view);
    }
}
