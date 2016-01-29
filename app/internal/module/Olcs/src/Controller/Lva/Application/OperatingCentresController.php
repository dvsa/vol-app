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
}
