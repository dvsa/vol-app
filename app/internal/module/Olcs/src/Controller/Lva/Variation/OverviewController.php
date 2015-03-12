<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Controller\Lva\Traits\ApplicationOverviewTrait;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractController
{
    use VariationControllerTrait,
        ApplicationOverviewTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
