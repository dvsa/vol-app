<?php

/**
 * External Application Payment Submission Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractPaymentSubmissionController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

use Common\Service\Entity\ApplicationEntityService as ApplicationService;
use Common\Service\Entity\LicenceEntityService as LicenceService;

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
