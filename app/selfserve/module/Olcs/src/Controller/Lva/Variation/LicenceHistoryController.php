<?php

/**
 * External Variation Licence History Controller
 *
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

class LicenceHistoryController extends Lva\AbstractLicenceHistoryController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';
}
