<?php

/**
 * Application Withdraw Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractWithdrawController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Application Withdraw Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class WithdrawController extends AbstractWithdrawController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
