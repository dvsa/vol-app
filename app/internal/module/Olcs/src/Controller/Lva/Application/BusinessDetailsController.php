<?php

/**
 * Internal Application Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Common\Controller\Lva\AbstractBusinessDetailsController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Internal Application Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetailsController extends AbstractBusinessDetailsController implements ApplicationFurnitureInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
