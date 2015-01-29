<?php

/**
 * External Variation Payment Submission Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractPaymentSubmissionController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Service\Entity\LicenceEntityService as Licence;

/**
 * External Variation Payment Submission Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PaymentSubmissionController extends AbstractPaymentSubmissionController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';

    protected function getTaskDescription($applicationId)
    {
        $isUpgrade = $this->getServiceLocator()->get('Processing\VariationSection')
            ->isLicenceUpgrade($applicationId);

        $applicationData = $this->getServiceLocator()->get('Entity\Application')
            ->getDataForPaymentSubmission($applicationId);

        if ($applicationData['goodsOrPsv']['id'] === Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return $isUpgrade ? 'GV80A Application' : 'GV81 Application';
        }

        return $isUpgrade ? 'PSV431A Application' : 'PSV431 Application';
    }
}
