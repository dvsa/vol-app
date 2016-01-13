<?php

/**
 * TrailersController.php
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * Class TrailersController
 *
 * {@inheritdoc}
 *
 * @package Olcs\Controller\Lva\Licence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class TrailersController extends Lva\AbstractTrailersController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';
}
