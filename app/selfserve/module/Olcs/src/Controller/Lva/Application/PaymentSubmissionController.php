<?php

/**
 * External Application Payment Submission Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

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

    protected function getTaskDescription($applicationId)
    {
        return 'GV79 Application'; // @TODO this shouldn't be hardcoded
    }
}
