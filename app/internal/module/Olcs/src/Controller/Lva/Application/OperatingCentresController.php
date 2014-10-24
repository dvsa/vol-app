<?php

/**
 * Internal Application Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Controller\Lva\Traits\ApplicationOperatingCentresControllerTrait;

/**
 * Internal Application Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController
{
    use ApplicationControllerTrait,
        ApplicationOperatingCentresControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
