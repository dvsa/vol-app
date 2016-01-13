<?php

/**
 * External Application People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PeopleController extends Lva\AbstractPeopleController
{
    use ApplicationControllerTrait {
        ApplicationControllerTrait::postSave as commonPostSave;
    }

    protected $lva = 'application';
    protected $location = 'external';
}
