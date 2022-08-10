<?php

/**
 * Internal Variation Licence History Controller
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Controller\Interfaces\VariationControllerInterface;

/**
 * Internal Variation Licence History Controller
 */
class LicenceHistoryController extends Lva\AbstractLicenceHistoryController implements
    VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
