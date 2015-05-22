<?php

/**
 * Application Grant Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\AbstractGrantController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Service\Entity\LicenceEntityService as Licence;

/**
 * Application Grant Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GrantController extends AbstractGrantController implements ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';

    /**
     * Check that the application can be granted
     *
     * @param int $applicationId
     * @param bool $isPost
     * @param array $post
     * @return array Array of error messages, empty if no validation errors
     */
    protected function validateGrantConditions($applicationId, $isPost = false, $post = [])
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

        // check inspection request
        if ($isPost && (!isset($post['inspection-request-confirm']['createInspectionRequest']) ||
            !$post['inspection-request-confirm']['createInspectionRequest'])) {
            $errors[] = 'application-grant-please-confirm-inspection-request';
        }

        // check inspection request / term
        if ($isPost && (isset($post['inspection-request-confirm']['createInspectionRequest']) &&
            $post['inspection-request-confirm']['createInspectionRequest'] === 'Y' &&
            !isset($post['inspection-request-grant-details']['dueDate']))) {
            $errors[] = 'application-grant-provide-due-date';
        }

        if ($this->shouldValidateEnforcementArea($applicationId)) {
            // check enforcement area status
            if (!$processingService->enforcementAreaIsValid($applicationId)) {
                 $errors[] = 'application-grant-error-enforcement-area';
            }
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

    /**
     * Alter grant form
     *
     * @param Common\Service\Form $form
     * @return Common\Service\Form
     */
    protected function alterGrantForm($form)
    {
        return $form;
    }

    /**
     * Maybe set confirm grant application message
     *
     * @param Common\Service\Form $form
     * @return Common\Service\Form
     */
    protected function maybeSetConfirmGrantApplication($form)
    {
        return $form;
    }

    /**
     * Maybe remove inspection request question
     *
     * @param Common\Service\Form $form
     * @return Common\Service\Form
     */
    protected function maybeRemoveInspectionRequestQuestion($form)
    {
        $this->getServiceLocator()->get('Helper\Form')->remove($form, 'inspection-request-details');
        $this->getServiceLocator()->get('Helper\Form')->remove($form, 'inspection-request-confirm');
        return $form;
    }

    /**
     * Maybe load scripts
     */
    protected function maybeLoadScripts()
    {
        $this->getServiceLocator()->get('Script')->loadFiles(['forms/confirm-grant']);
    }

    /**
     * Should enforcement area be validated
     *
     * @param int $applicationId
     *
     * @return bool
     */
    protected function shouldValidateEnforcementArea($applicationId)
    {
        $application = $this->getServiceLocator()->get('Entity\Application')->getTypeOfLicenceData($applicationId);
        // don't validate enforcement area if PSV special restricted
        return !($application['goodsOrPsv'] === Licence::LICENCE_CATEGORY_PSV &&
            $application['licenceType'] === Licence::LICENCE_TYPE_SPECIAL_RESTRICTED);
    }
}
