<?php

/**
 * External Application Payment Submission Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Dvsa\Olcs\Application\Controller;

use Olcs\Controller\Lva\AbstractPaymentSubmissionController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Payment Submission Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PaymentSubmissionController extends AbstractPaymentSubmissionController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
