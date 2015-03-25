<?php

/**
 * Variation Withdraw Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractWithdrawController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Variation Withdraw Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class WithdrawController extends AbstractWithdrawController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
