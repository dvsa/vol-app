<?php

/**
 * Class TrailersController
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * Class TrailersController
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TrailersController extends Lva\AbstractTrailersController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';
}
