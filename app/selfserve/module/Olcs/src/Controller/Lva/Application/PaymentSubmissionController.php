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

    protected function getTaskDescription($applicationId)
    {
        $applicationService = $this->getLvaEntityService('Entity\Application');

        $applicationData = $applicationService->getDataForValidating($applicationId);

        switch ($applicationData['goodsOrPsv']) {
            case LicenceService::LICENCE_CATEGORY_GOODS_VEHICLE:
                return ApplicationService::CODE_GV_APP . ' Application';
            case LicenceService::LICENCE_CATEGORY_PSV:
                if ($applicationData['licenceType'] === LicenceService::LICENCE_TYPE_SPECIAL_RESTRICTED) {
                    return ApplicationService::CODE_PSV_APP_SR . ' Application';
                }

                return ApplicationService::CODE_PSV_APP . ' Application';
            default:
                return 'Application';
        }
    }

    /**
     * Update licence status after application submission
     *
     * @var int applicationId
     */
    protected function updateLicenceStatus($applicationId)
    {
        $licenceId = $this->getServiceLocator()
                    ->get('Entity\Application')
                    ->getLicenceIdForApplication($applicationId);
        $this->getServiceLocator()
            ->get('Entity\Licence')
            ->forceUpdate(
                $licenceId,
                [
                    'status' => LicenceService::LICENCE_STATUS_UNDER_CONSIDERATION
                ]
            );
    }
}
