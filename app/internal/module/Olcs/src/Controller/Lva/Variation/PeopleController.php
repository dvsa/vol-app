<?php

/**
 * Internal Variation People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Controller\Interfaces\VariationControllerInterface;

/**
 * Internal Variation People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PeopleController extends Lva\AbstractPeopleController implements VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';

    public function disqualifyAction()
    {
        return $this->forward()->dispatch(
            \Olcs\Controller\DisqualifyController::class,
            [
                'action' => 'index',
                'variation' => $this->params()->fromRoute('application'),
                'person' => $this->params()->fromRoute('child_id')
            ]
        );
    }
}
