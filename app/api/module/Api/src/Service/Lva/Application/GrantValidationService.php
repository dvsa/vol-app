<?php

namespace Dvsa\Olcs\Api\Service\Lva\Application;

use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Lva\SectionAccessService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * GrantValidationService
 *
 * Validate an application/variation prior to Granting
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GrantValidationService implements FactoryInterface
{
    public const ERROR_S4_EMPTY = 'APP-GRA-S4-EMPTY';
    public const ERROR_OOOD_UNKNOWN = 'APP-GRA-OOOD-UNKNOWN';
    public const ERROR_OORD_UNKNOWN = 'APP-GRA-OORD-UNKNOWN';
    public const ERROR_OOOD_NOT_PASSED = 'APP-GRA-OOOD-NOT-PASSED';
    public const ERROR_OORD_NOT_PASSED = 'APP-GRA-OORD-NOT-PASSED';

    /**
     * @var SectionAccessService
     */
    private $sectionAccessService;

    /**
     * Validate the application
     *
     *
     * @return array of validation error messages
     */
    public function validate(ApplicationEntity $application)
    {
        if ($application->isVariation()) {
            return $this->getGrantVariationMessages($application);
        }

        return $this->getGrantApplicationMessages($application);
    }

    protected function getGrantApplicationMessages(ApplicationEntity $application)
    {
        $errors = array_merge(
            $this->validateOpposition($application),
            $this->validateS4($application)
        );

        $accessible = $this->getAccessibleSections($application);

        if (!$application->getApplicationTracking()->isValid($accessible)) {
            $errors['application-grant-error-tracking'] = 'application-grant-error-tracking';
        }

        $required = $this->getRequiredApplicationSections($application);

        if (!$application->getApplicationCompletion()->isComplete($required)) {
            $missingSections = $application->getApplicationCompletion()->getIncompleteSections($required);
            $errors['application-grant-error-sections'] = $missingSections;
        }

        if (!$this->feeStatusIsValid($application)) {
            $errors['application-grant-error-fees'] = 'application-grant-error-fees';
        }

        // check enforcement area status
        if (
            $this->shouldValidateEnforcementArea($application)
            && !$this->enforcementAreaIsValid($application)
        ) {
            $errors['application-grant-error-enforcement-area'] = 'application-grant-error-enforcement-area';
        }

        return $errors;
    }

    protected function getGrantVariationMessages(ApplicationEntity $application)
    {
        $errors = array_merge(
            $this->validateOpposition($application),
            $this->validateS4($application)
        );

        $accessible = $this->getAccessibleSections($application);

        if (!$application->getApplicationTracking()->isValid($accessible)) {
            $errors['application-grant-error-tracking'] = 'application-grant-error-tracking';
        }

        if (!$application->hasVariationChanges()) {
            $errors['variation-grant-error-no-change'] = 'variation-grant-error-no-change';
        } else {
            $incompleteSections = $application->getSectionsRequiringAttention();

            if (!empty($incompleteSections)) {
                $errors['variation-grant-error-sections'] = $incompleteSections;
            }
        }

        // check fee status
        if (!$this->feeStatusIsValid($application)) {
            $errors['application-grant-error-fees'] = 'application-grant-error-fees';
        }

        return $errors;
    }

    protected function getAccessibleSections(ApplicationEntity $application)
    {
        $accessible = $this->sectionAccessService->getAccessibleSections($application);
        return array_keys($accessible);
    }

    protected function shouldValidateEnforcementArea(ApplicationEntity $application)
    {
        // don't validate enforcement area if PSV special restricted
        return ($application->isGoods() || !$application->isSpecialRestricted());
    }

    protected function enforcementAreaIsValid(ApplicationEntity $application)
    {
        return $application->getLicence()->getEnforcementArea() !== null;
    }

    protected function feeStatusIsValid(ApplicationEntity $application)
    {
        // Get outstanding fees
        $fees = $application->getFees()->filter(
            fn($element) => in_array(
                $element->getFeeStatus(),
                [
                    Fee::STATUS_OUTSTANDING,
                ]
            ) && in_array(
                $element->getFeeType()->getFeeType()->getId(),
                [RefData::FEE_TYPE_VAR, RefData::FEE_TYPE_APP]
            )
        );

        return $fees->count() < 1;
    }

    protected function getRequiredApplicationSections(ApplicationEntity $application)
    {
        $requiredSections = [
            'type_of_licence',
            'business_type',
            'business_details',
            'addresses',
            'people',
        ];
        if ($application->isSpecialRestricted()) {
            $requiredSections[] = 'taxi_phv';
        } else {
            $requiredSections[] = 'operating_centres';
        }

        return $requiredSections;
    }

    /**
     * Validate S4
     *
     *
     * @return array of validation messages
     */
    private function validateS4(ApplicationEntity $application)
    {
        $errors = [];
        // If the there is a schedule 4/1 and the schedule 4/1 status is empty then generate an error
        if ($application->getS4s()->count() > 0) {
            /* @var $s4 \Dvsa\Olcs\Api\Entity\Application\S4 */
            foreach ($application->getS4s() as $s4) {
                if (empty($s4->getOutcome())) {
                    $errors[self::ERROR_S4_EMPTY] = 'You must decide the schedule 4/1 before granting the application';
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * Validate Oppossition
     *
     *
     * @return array of validation messages
     */
    private function validateOpposition(ApplicationEntity $application)
    {
        // If the Override opposition dates is ticked then do not check the representation/opposition dates
        if ($application->getOverrideOoo() === 'Y') {
            return [];
        }

        $errors = [];
        $oood = $application->getOutOfOppositionDate();
        // Display an additional error if the Out of opposition date is 'Unknown'
        if ($oood === ApplicationEntity::UNKNOWN) {
            $errors[self::ERROR_OOOD_UNKNOWN] = 'The out of opposition date cannot be unknown.';
        }

        $oord = $application->getOutOfRepresentationDate();
        // Display an additional error if the Out of Representation date is 'Unknown'
        if ($oord === ApplicationEntity::UNKNOWN) {
            $errors[self::ERROR_OORD_UNKNOWN] = 'The out of representation date cannot be unknown';
        }

        // Display an additional error if the Out of opposition date is after the current date
        if (
            $oood instanceof \DateTime && $oood > new \DateTime()
        ) {
            $errors[self::ERROR_OOOD_NOT_PASSED] = 'The out of opposition period has not yet passed';
        }

        // Display an additional error if the Out of representation date is after the current date
        if (
            $oord instanceof \DateTime && $oord > new \DateTime()
        ) {
            $errors[self::ERROR_OORD_NOT_PASSED] = 'The out of representation date has not yet passed';
        }

        return $errors;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->sectionAccessService = $container->get('SectionAccessService');
        return $this;
    }
}
