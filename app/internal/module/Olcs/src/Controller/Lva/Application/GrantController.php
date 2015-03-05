<?php

/**
 * Application Grant Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractGrantController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Service\Entity\LicenceEntityService as Licence;

/**
 * Application Grant Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class GrantController extends AbstractGrantController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';

    /**
     * Check that the application can be granted
     *
     * @param int $applicationId
     * @return array Array of error messages, empty if no validation errors
     */
    protected function validateGrantConditions($applicationId)
    {
        $errors = [];
        $processingService = $this->getServiceLocator()->get('Processing\Application');

        // check tracking status
        $required = $this->getAccessibleSections();
        if (!$processingService->trackingIsValid($applicationId, $required)) {
            $errors[] = 'application-grant-error-tracking';
        }

        // check section completion
        $required = $this->getRequiredSections($applicationId);
        if (!$processingService->sectionCompletionIsValid($applicationId, $required)) {
            $translator = $this->getServiceLocator()->get('Helper\Translation');
            $missingSections = $processingService->getIncompleteSections($applicationId, $required);
            $sections = array_map(
                function ($section) use ($translator) {
                    return $translator->translate('lva.section.title.'.$section);
                },
                $missingSections
            );
            $errors[] = $translator->translateReplace(
                'application-grant-error-sections',
                [implode(', ', $sections)]
            );
        }

        // check fee status
        if (!$processingService->feeStatusIsValid($applicationId)) {
             $errors[] = 'application-grant-error-fees';
        }

        return $errors;
    }

    /**
     *  The key sections for Goods and PSV (excluding special restricted) are:
     *   Type of Licence
     *   Business type
     *   Business details
     *   Addresses
     *   People
     *   Operating centres & Authorisation
     *  For special restricted it is the same list except that ‘Taxi/PHV’ is
     *  required instead of Operating centres & Authorisation
     * @param int $applicationId
     * @return array
     */
    protected function getRequiredSections($applicationId)
    {
        $requiredSections = [
            'type_of_licence',
            'business_type',
            'business_details',
            'addresses',
            'people',
        ];

        $licenceType = $this->getTypeOfLicenceData()['licenceType'];
        if ($licenceType === Licence::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            $requiredSections[] = 'taxi_phv';
        } else {
            $requiredSections[] = 'operating_centres';
        }

        return $requiredSections;
    }
}
