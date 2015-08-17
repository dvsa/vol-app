<?php

/**
 * Internal Licence People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * Internal Licence People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PeopleController extends Lva\AbstractPeopleController implements LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';

    public function disqualifyAction()
    {
        return $this->forward()->dispatch(
            \Olcs\Controller\DisqualifyController::class,
            [
                'action' => 'index',
                'licence' => $this->params()->fromRoute('licence'),
                'person' => $this->params()->fromRoute('child_id')
            ]
        );
    }
}
