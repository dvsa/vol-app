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
use Common\Service\Entity\ApplicationEntityService as Application;

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
            $code = $isUpgrade ? Application::CODE_GV_VAR_UPGRADE : Application::CODE_GV_VAR_NO_UPGRADE;
        } else {
            $code = $isUpgrade ? Application::CODE_PSV_VAR_UPGRADE : Application::CODE_PSV_VAR_NO_UPGRADE;
        }

        return $code . ' Application';
    }

    protected function updateLicenceStatus($applicationId)
    {
        // no-op
    }
}
