<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Laminas\Filter\Word\CamelCaseToUnderscore;
use Laminas\Filter\Word\UnderscoreToCamelCase;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Service\VariationOperatingCentreHelper;
use Dvsa\Olcs\Api\Domain\Service\UpdateOperatingCentreHelper;
use Dvsa\Olcs\Api\Service\FinancialStandingHelperService;
use Dvsa\Olcs\Transfer\Command\Application\UpdateOperatingCentres as UpdateOperatingCentresCmd;
use Psr\Container\ContainerInterface;

/**
 * @see \Dvsa\Olcs\Transfer\Command\Application\UpdateOperatingCentres
 * @see \Dvsa\OlcsTest\Api\Domain\CommandHandler\Application\UpdateVariationCompletionTest
 */
class UpdateVariationCompletion extends AbstractCommandHandler implements
    TransactionedInterface,
    AuthAwareInterface
{
    use AuthAwareTrait;

    public const STATUS_UNCHANGED = Application::VARIATION_STATUS_UNCHANGED;
    public const STATUS_UPDATED = Application::VARIATION_STATUS_UPDATED;
    public const STATUS_REQUIRES_ATTENTION = Application::VARIATION_STATUS_REQUIRES_ATTENTION;

    protected $repoServiceName = 'Application';

    private $requireAttentionMap = [
        'type_of_licence' => [],
        'business_type' => [],
        'business_details' => [
            'business_type'
        ],
        'addresses' => [],
        'people' => [],
        'operating_centres' => [],
        'financial_evidence' => [],
        'transport_managers' => [],
        'vehicles' => [],
        'discs' => [],
        'community_licences' => [],
        'safety' => [],
        'conditions_undertakings' => [],
        'financial_history' => [],
        'licence_history' => [],
        'convictions_penalties' => [],
        //'undertakings' => [] We don't want this as there is bespoke rules around setting this status
    ];

    private $sectionUpdatedCheckMap = [
        'type_of_licence' => 'hasUpdatedTypeOfLicence',
        'business_type' => 'hasUpdatedBusinessType',
        'business_details' => 'hasUpdatedBusinessDetails',
        'addresses' => 'hasUpdatedAddresses',
        'people' => 'hasUpdatedPeople',
        'operating_centres' => 'hasUpdatedOperatingCentres',
        'financial_evidence' => 'hasSavedSection',
        'transport_managers' => 'hasUpdatedTransportManagers',
        'vehicles' => 'hasUpdatedVehicles',
        'vehicles_psv' => 'hasUpdatedVehicles',
        'vehicles_size' => 'hasSavedSection',
        'psv_operate_large' => 'hasSavedSection',
        'psv_operate_small' => 'hasSavedSection',
        'psv_small_part_written' => 'hasSavedSection',
        'psv_small_conditions' => 'hasSavedSection',
        'psv_documentary_evidence_small' => 'hasSavedSection',
        'psv_documentary_evidence_large' => 'hasSavedSection',
        'psv_operate_novelty' => 'hasSavedSection',
        'psv_main_occupation_undertakings' => 'hasSavedSection',
        'discs' => 'hasSavedSection',
        'community_licences' => 'hasSavedSection',
        'safety' => 'hasUpdatedSafetySection',
        'conditions_undertakings' => 'hasUpdatedConditionsUndertakings',
        'financial_history' => 'hasUpdatedFinancialHistory',
        'licence_history' => 'hasSavedSection',
        'convictions_penalties' => 'hasUpdatedConvictionsPenalties',
        'undertakings' => 'hasUpdatedUndertakings',
        'declarations_internal' => 'hasUpdateDeclarationsInternal',
    ];

    protected $bespokeRulesMap = [
        'type_of_licence' => 'updateRelatedTypeOfLicenceSections',
        'operating_centres' => 'updateRelatedOperatingCentreSections',
        'people' => 'updateRelatedPeopleSections',
        'transport_managers' => 'updateRelatedTmSections',
        'vehicles' => 'updateRelatedVehiclesSections',
        'vehicles_size' => 'updateRelatedVehiclesSizeSections',
        'discs' => 'updateRelatedDiscSections',
        'community_licences' => 'updateRelatedCommunityLicencesSections'
    ];

    /**
     * @var Application
     */
    private $application;

    /**
     * @var Licence
     */
    private $licence;

    /**
     * @var array
     */
    private $suffixes = [];

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var UpdateOperatingCentreHelper
     */
    private $updateHelper;

    /**
     * @var VariationOperatingCentreHelper
     */
    private $variationHelper;

    /**
     * @var FinancialStandingHelperService
     */
    private $financialStandingHelper;

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $section = $command->getSection();
        $this->data = $command->getData();

        $filter = new CamelCaseToUnderscore();
        $section = strtolower((string) $filter->filter($section));

        $this->application = $this->getRepo()->fetchUsingId($command);
        $this->licence = $this->application->getLicence();

        if (!$this->hasSectionChanged($section)) {
            $this->markSectionUnchanged($section);
        } elseif (!$this->isUpdated($section)) {
            $this->markSectionUpdated($section);
            if ($section !== 'undertakings' && $section !== 'declarations_internal') {
                $this->resetUndertakings();
            }
        }

        $this->updateSectionsRequiringAttention($section);

        $this->applyBespokeRules();

        $this->getRepo()->save($this->application);

        $result = new Result();
        $result->addMessage('Updated variation completion status');

        return $result;
    }

    /**
     * Method to call the corresponding business rule
     *
     * @param string $section section
     *
     * @return boolean
     */
    private function hasSectionChanged($section)
    {
        return $this->{$this->sectionUpdatedCheckMap[$section]}();
    }

    /**
     * Business rules to check if the TOL section has been updated
     *
     * @return boolean
     */
    protected function hasUpdatedTypeOfLicence()
    {
        return $this->application->getLicenceType() !== $this->licence->getLicenceType() ||
            $this->application->getVehicleType() != $this->licence->getVehicleType();
    }

    /**
     * Has updated business type
     *
     * @return bool
     */
    protected function hasUpdatedBusinessType()
    {
        return ($this->data['type'] !== $this->licence->getOrganisation()->getType()->getId());
    }

    /**
     * Has updated business details
     *
     * @return bool
     */
    protected function hasUpdatedBusinessDetails()
    {
        // If requires attention or already updated, then we mark the section as updated
        if (!$this->isUnchanged('business_details')) {
            return true;
        }

        // Otherwise we check for an actual change
        if ($this->data['hasChanged']) {
            return true;
        }

        return false;
    }

    /**
     * Has updated address
     *
     * @return bool
     */
    protected function hasUpdatedAddresses()
    {
        // If requires attention or already updated, then we mark the section as updated
        if (!$this->isUnchanged('addresses')) {
            return true;
        }

        // Otherwise we check for an actual change
        if ($this->data['hasChanged']) {
            return true;
        }

        return false;
    }

    /**
     * Has updated people
     *
     * @return bool
     */
    protected function hasUpdatedPeople()
    {
        // If requires attention or already updated, then we mark the section as updated
        if (!$this->isUnchanged('people')) {
            return true;
        }

        return !$this->application->getApplicationOrganisationPersons()->isEmpty();
    }

    /**
     * Has updated safety section
     *
     * @return bool
     */
    protected function hasUpdatedSafetySection()
    {
        // Otherwise we check for an actual change
        if ($this->data['hasChanged']) {
            return true;
        }

        return false;
    }

    /**
     * A generic callback that marks a section as complete
     *
     * @return boolean
     */
    protected function hasSavedSection()
    {
        return true;
    }

    /**
     * Business rules to check if the OC section has been updated
     *
     * @return boolean
     */
    protected function hasUpdatedOperatingCentres()
    {
        if (!$this->isUnchanged('operating_centres')) {
            return true;
        }

        return $this->hasActuallyUpdatedOperatingCentres();
    }

    /**
     * Has actually updated operating centres
     *
     * @return bool
     */
    protected function hasActuallyUpdatedOperatingCentres()
    {
        if ($this->application->getOperatingCentres()->count() > 0) {
            return true;
        }

        return $this->application->hasAuthChanged();
    }

    /**
     * If we have updated the transport manager section
     *
     * @return boolean
     */
    protected function hasUpdatedTransportManagers()
    {
        return ($this->application->getTransportManagers()->count() > 0);
    }

    /**
     * If we have updated the vehicles section
     *
     * @return boolean
     */
    protected function hasUpdatedVehicles()
    {
        return ($this->application->getLicenceVehicles()->count() > 0);
    }

    /**
     * If we have updated conditions undertakings
     *
     * @return boolean
     */
    protected function hasUpdatedConditionsUndertakings()
    {
        return ($this->application->getConditionUndertakings()->count() > 0);
    }

    /**
     * If we have updated financial history
     *
     * @return boolean
     */
    protected function hasUpdatedFinancialHistory()
    {
        $fields = [
            'Bankrupt',
            'Administration',
            'Disqualified',
            'Liquidation',
            'Receivership',
            'InsolvencyConfirmation',
            'InsolvencyDetails'
        ];

        return $this->hasCompletedFields($fields);
    }

    /**
     * If we have updated convictions penalties section
     *
     * @return boolean
     */
    protected function hasUpdatedConvictionsPenalties()
    {
        if ($this->application->getConvictionsConfirmation() !== 0) {
            return true;
        }

        if ($this->application->getPrevConviction() !== null) {
            return true;
        }

        return false;
    }

    /**
     * If we have updated undertakings
     *
     * @return boolean
     */
    protected function hasUpdatedUndertakings()
    {
        return ($this->application->getDeclarationConfirmation() === 'Y');
    }

    /**
     * Has update declarations internal
     *
     * @return bool
     */
    protected function hasUpdateDeclarationsInternal()
    {
        return ($this->application->getAuthSignature());
    }


    /**
     * If we have completed at least 1 of the fields in the list
     *
     * @param array $fields field
     *
     * @return boolean
     */
    protected function hasCompletedFields($fields)
    {
        foreach ($fields as $field) {
            $value = $this->application->{'get' . $field}();
            if (!empty($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mark a section as required
     *
     * @param string $section section
     *
     * @return void
     */
    protected function markSectionRequired($section)
    {
        $this->markSectionStatus($section, self::STATUS_REQUIRES_ATTENTION);
    }

    /**
     * Mark a section as unchanged
     *
     * @param string $section section
     *
     * @return void
     */
    protected function markSectionUnchanged($section)
    {
        $this->markSectionStatus($section, self::STATUS_UNCHANGED);
    }

    /**
     * Mark a section as updated
     *
     * @param string $section section
     *
     * @return void
     */
    protected function markSectionUpdated($section)
    {
        $this->markSectionStatus($section, self::STATUS_UPDATED);
    }

    /**
     * Mark a section with the given status
     *
     * @param string $section section
     * @param int    $status  status
     *
     * @return void
     */
    protected function markSectionStatus($section, $status)
    {
        $setter = 'set' . $this->getSectionAsSuffix($section) . 'Status';

        $this->application->getApplicationCompletion()->$setter($status);
    }

    /**
     * Check if a section has been updated
     *
     * @param string $section section
     *
     * @return boolean
     */
    protected function isUpdated($section)
    {
        return $this->isStatus($section, self::STATUS_UPDATED);
    }

    /**
     * Check if the section is unchanged
     *
     * @param string $section section
     *
     * @return string
     */
    protected function isUnchanged($section)
    {
        return $this->isStatus($section, self::STATUS_UNCHANGED);
    }

    /**
     * Check if the section requires attention
     *
     * @param string $section section
     *
     * @return string
     */
    protected function doesRequireAttention($section)
    {
        return $this->isStatus($section, self::STATUS_REQUIRES_ATTENTION);
    }

    /**
     * Shared logic to check a sections status
     *
     * @param string $section section
     * @param int    $status  status
     *
     * @return boolean
     */
    protected function isStatus($section, $status)
    {
        $getter = 'get' . $this->getSectionAsSuffix($section) . 'Status';

        return (int)$this->application->getApplicationCompletion()->$getter() == (int)$status;
    }

    /**
     * Get section as suffix
     *
     * @param string $section section
     *
     * @return mixed
     */
    protected function getSectionAsSuffix($section)
    {
        if (empty($this->suffixes[$section])) {
            $filter = new UnderscoreToCamelCase();
            $this->suffixes[$section] = $filter->filter($section);
        }

        return $this->suffixes[$section];
    }

    /**
     * Reset the undertakings section
     *
     * @return void
     */
    protected function resetUndertakings()
    {
        $this->application->setDeclarationConfirmation('N');

        if (!$this->isInternalUser()) {
            $this->markSectionRequired('undertakings');
        }
    }

    /**
     * Apply the generic rules on sections requiring attention
     *
     * @param string $currentSection current section
     *
     * @return void
     */
    protected function updateSectionsRequiringAttention($currentSection)
    {
        foreach ($this->requireAttentionMap as $section => $triggers) {
            // Skip the current section, or updated sections
            if ($section === $currentSection || $this->isUpdated($section)) {
                continue;
            }

            // Mark each section as unchanged
            $this->markSectionUnchanged($section);

            // Unless the related sections have been updated
            foreach ($triggers as $trigger) {
                if ($this->isUpdated($trigger)) {
                    $this->markSectionRequired($section);
                }
            }
        }
    }

    /**
     * Some sections have more complicated rules, we hook into these here
     *
     * @return void
     */
    protected function applyBespokeRules()
    {
        foreach ($this->bespokeRulesMap as $section => $callback) {
            if ($this->isUpdated($section)) {
                $this->$callback();
            }
        }
    }

    protected function updateRelatedVehiclesSizeSections(): void
    {
        // If the section is no longer required then remove them
        if (!$this->isPsv() || !$this->application->hasAuthChanged()) {
            $allSections = [
                'vehicles_size',
                'psv_operate_large',
                'psv_operate_small',
                'psv_small_part_written',
                'psv_small_conditions',
                'psv_documentary_evidence_small',
                'psv_documentary_evidence_large',
                'psv_operate_novelty',
                'psv_main_occupation_undertakings',
            ];

            foreach ($allSections as $section) {
                $this->markSectionUnchanged($section);
            }

            return;
        }

        $relatedSections = [];
        $ignoredSections = [];

        if ($this->application->isPsvVehicleSizeSmall()) {
            $relatedSections = [
                'psv_small_conditions',
                'psv_documentary_evidence_small',
                'psv_operate_novelty',
            ];

            $ignoredSections = [
                'psv_operate_small',
                'psv_operate_large',
                'psv_small_part_written',
                'psv_documentary_evidence_large',
                'psv_main_occupation_undertakings',
            ];
        } elseif ($this->application->isPsvVehicleSizeMediumLarge()) {
            $relatedSections = [
                'psv_operate_large',
                'psv_documentary_evidence_large',
                'psv_operate_novelty',
                'psv_main_occupation_undertakings',
            ];

            $ignoredSections = [
                'psv_operate_small',
                'psv_small_conditions',
                'psv_documentary_evidence_small',
                'psv_small_part_written',
            ];
        } elseif ($this->application->isPsvVehicleSizeBoth()) {
            if ($this->application->isOperatingSmallPsvAsPartOfLarge()) {
                $relatedSections = [
                    'psv_operate_small',
                    'psv_small_conditions',
                    'psv_documentary_evidence_large',
                    'psv_operate_novelty',
                    'psv_small_part_written',
                    'psv_main_occupation_undertakings',
                ];

                $ignoredSections = [
                    'psv_operate_large',
                    'psv_documentary_evidence_small',
                ];
            } elseif ($this->application->isPsvBothNotOperatingSmallPsvAsPartOfLarge()) {
                $relatedSections = [
                    'psv_operate_small',
                    'psv_small_conditions',
                    'psv_documentary_evidence_large',
                    'psv_operate_novelty',
                    'psv_documentary_evidence_small',
                    'psv_main_occupation_undertakings',
                ];

                $ignoredSections = [
                    'psv_operate_large',
                    'psv_small_part_written',
                ];
            } else {
                $relatedSections = [
                    'psv_operate_small',
                    'psv_documentary_evidence_large',
                    'psv_operate_novelty',
                    'psv_main_occupation_undertakings',
                ];

                $ignoredSections = [
                    'psv_operate_large',
                    'psv_small_part_written',
                    'psv_small_conditions',
                    'psv_documentary_evidence_small',
                ];
            }
        }

        //restricted licences have extra sections - in theory this doesn't matter for variations, but included to future-proof
        if (!$this->application->isRestricted()) {
            $restrictedOnlySections = [
                'psv_main_occupation_undertakings',
                'psv_documentary_evidence_large',
            ];

            foreach ($restrictedOnlySections as $restrictedOnlySection) {
                if (($key = array_search($restrictedOnlySection, $relatedSections, true)) !== false) {
                    unset($relatedSections[$key]);
                    $ignoredSections[] = $restrictedOnlySection;
                }
            }
        }

        foreach ($relatedSections as $section) {
            if ($this->isUnchanged($section)) {
                $this->markSectionRequired($section);
            }
        }

        foreach ($ignoredSections as $section) {
            $this->markSectionUnchanged($section);
        }
    }

    /**
     * Apply bespoke type of licence rules
     *
     * @return void
     */
    protected function updateRelatedTypeOfLicenceSections()
    {
        // If the old licence type was restricted and it is being upgraded
        if ($this->application->isLicenceUpgrade()) {
            $relatedSections = [
                'addresses',
                'financial_evidence',
                'transport_managers',
                'financial_history',
                'convictions_penalties'
            ];

            foreach ($relatedSections as $section) {
                if ($this->isUnchanged($section)) {
                    $this->markSectionRequired($section);
                }
            }
        }

        if (
            ($this->application->isPsvDowngrade() && $this->isUnchanged('operating_centres'))
            || ($this->application->isLicenceChangeWhichRequiresOperatingCentre()
                && $this->application->getOperatingCentres()->isEmpty()
            )
        ) {
            $this->markSectionRequired('operating_centres');
        }

        if ($this->application->canHaveTrailer() && $this->licence->getSafetyInsTrailers() === null) {
            // can have trailer but safety inspections on trailers is not set
            // mark the safety section as required
            $this->markSectionRequired('safety');
        }
    }

    /**
     * Apply the operating centre rules
     *
     * @return void
     */
    protected function updateRelatedOperatingCentreSections()
    {
        // If the financial evidence section is unchanged (Not requires attention or updated)
        // ...and we have increased the total auth vehicles
        if ($this->isUnchanged('financial_evidence') && $this->hasRequiredFinanceAmountIncreased()) {
            $this->markSectionRequired('financial_evidence');
        }

        $vehSection = $this->getRelevantVehicleSection();

        // If the vehicle section is unchanged AND the totAuthVehicles has dropped below the number of vehicles added
        if (!$this->isPsv() && $this->isUnchanged($vehSection) && $this->hasTotAuthVehiclesDroppedBelowVehicleCount()) {
            $this->markSectionRequired($vehSection);
        }

        // PSV rules only
        if ($this->isPsv()) {
            // If the discs section is unchanged AND the totAuthVehicles has dropped below the number of discs added
            if ($this->isUnchanged('discs') && $this->hasTotAuthVehiclesDroppedBelowDiscsCount()) {
                $this->markSectionRequired('discs');
            }

            // If the vehicles size section is unchanged and any of the tot auth vehicle columns has increased
            if ($this->isUnchanged('vehicles_size') && $this->application->hasAuthChanged()) {
                $this->markSectionRequired('vehicles_size');
            }
        }

        // If auth hasn't changed, and we are not downgrading
        if (!$this->hasActuallyUpdatedOperatingCentres() && $this->application->isPsvDowngrade() === false) {
            $this->markSectionUnchanged('operating_centres');
        }

        // If we have changed the auth, check that the Community Lic section is still ok
        if ($this->hasActuallyUpdatedOperatingCentres() && $this->isTotAuthLessThanComLic()) {
            $this->markSectionRequired('community_licences');
        }

        if (count($this->validateAuthority())) {
            $this->markSectionRequired('operating_centres');
        }
    }

    /**
     * Validate authority
     *
     * @return array
     */
    protected function validateAuthority()
    {
        $data = [
            'totAuthHgvVehicles' => $this->application->getTotAuthHgvVehicles(),
            'totAuthLgvVehicles' => $this->application->getTotAuthLgvVehicles(),
            'totAuthTrailers' => $this->application->getTotAuthTrailers(),
        ];
        $command = UpdateOperatingCentresCmd::create($data);
        $totals = $this->getAuthorityTotals();
        if ($this->application->isPsv()) {
            $this->updateHelper->validatePsv($this->application, $command);
        } else {
            $this->updateHelper->validateTotalAuthTrailers($this->application, $command, $totals);
        }

        $this->updateHelper->validateTotalAuthHgvVehicles($this->application, $command, $totals);
        $this->updateHelper->validateTotalAuthLgvVehicles($this->application, $command);

        return $this->updateHelper->getMessages();
    }

    /**
     * Get authority totals
     *
     * @return array
     */
    protected function getAuthorityTotals()
    {
        $aocs = $this->variationHelper->getListDataForApplication($this->application);

        $totals = [
            'noOfOperatingCentres' => 0,
            'minHgvVehicleAuth' => 0,
            'maxHgvVehicleAuth' => 0,
            'minTrailerAuth' => 0,
            'maxTrailerAuth' => 0,
        ];

        foreach ($aocs as $aoc) {
            if (in_array($aoc['action'], ['D', 'C'])) {
                continue;
            }

            $totals['noOfOperatingCentres']++;

            $totals['minHgvVehicleAuth'] = max([$totals['minHgvVehicleAuth'], $aoc['noOfVehiclesRequired']]);
            $totals['minTrailerAuth'] = max([$totals['minTrailerAuth'], $aoc['noOfTrailersRequired']]);

            $totals['maxHgvVehicleAuth'] += (int)$aoc['noOfVehiclesRequired'];
            $totals['maxTrailerAuth'] += (int)$aoc['noOfTrailersRequired'];
        }

        return $totals;
    }

    /**
     * Update related people sections
     *
     * @return void
     */
    protected function updateRelatedPeopleSections()
    {
        // Don't change if we require attention
        if ($this->doesRequireAttention('people')) {
            return;
        }

        $appOrgPersons = $this->application->getApplicationOrganisationPersons();
        if ($appOrgPersons->isEmpty()) {
            $this->markSectionUnchanged('people');
            return;
        }

        $markRequired = false;
        foreach ($appOrgPersons as $appOrgPerson) {
            if (in_array($appOrgPerson->getAction(), ['A', 'U'])) {
                $markRequired = true;
                break;
            }
        }

        if ($markRequired) {
            if (!$this->isUpdated('financial_history')) {
                $this->markSectionRequired('financial_history');
            }
            if (!$this->isUpdated('licence_history')) {
                $this->markSectionRequired('licence_history');
            }
            if (!$this->isUpdated('convictions_penalties')) {
                $this->markSectionRequired('convictions_penalties');
            }
        }

        $this->markSectionUpdated('people');
    }

    /**
     * Update related tm sections
     *
     * @return void
     */
    protected function updateRelatedTmSections()
    {
        if (!$this->hasUpdatedTransportManagers()) {
            if ($this->application->isLicenceUpgrade()) {
                $this->markSectionRequired('transport_managers');
            } else {
                $this->markSectionUnchanged('transport_managers');
            }
        }
    }

    /**
     * Update related vehicles sections
     *
     * @return void
     */
    protected function updateRelatedVehiclesSections()
    {
        $licenceVehicles = $this->licence->getActiveVehiclesCount();
        $applicationVehicles = $this->application->getActiveVehicles()->count();

        $totalVehicles = $licenceVehicles + $applicationVehicles;

        if ($totalVehicles > (int)$this->application->getTotAuthVehicles()) {
            $this->markSectionRequired($this->getRelevantVehicleSection());
            return;
        }

        if ($applicationVehicles < 1) {
            $this->markSectionUnchanged($this->getRelevantVehicleSection());
            return;
        }

        $this->markSectionUpdated($this->getRelevantVehicleSection());
    }

    /**
     * Update related disc sections
     *
     * @return void
     */
    protected function updateRelatedDiscSections()
    {
        if ((int)$this->application->getTotAuthVehicles() < (int)$this->licence->getPsvDiscsNotCeasedCount()) {
            $this->markSectionRequired('discs');
            return;
        }

        $this->markSectionUpdated('discs');
    }

    /**
     * Update related community licences sections
     *
     * @return void
     */
    protected function updateRelatedCommunityLicencesSections()
    {
        if ($this->isTotAuthLessThanComLic()) {
            $this->markSectionRequired('community_licences');
            return;
        }

        $this->markSectionUpdated('community_licences');
    }

    /**
     * Is total authority less than community licences count
     *
     * @return bool
     */
    protected function isTotAuthLessThanComLic()
    {
        $activeComLics = (int)$this->licence->getActiveCommunityLicences()->count();
        $totAuth = (int)$this->application->getTotAuthVehicles();

        return ($totAuth < $activeComLics);
    }

    /**
     * Check whether the required finance amount has increased
     *
     * @return boolean
     */
    protected function hasRequiredFinanceAmountIncreased()
    {
        $requiredFinanceWithVariation = $this->financialStandingHelper->getRequiredFinance($this->application);

        $requiredFinanceWithoutVariation = $this->financialStandingHelper->getRequiredFinance(
            $this->application,
            false
        );

        return $requiredFinanceWithVariation > $requiredFinanceWithoutVariation;
    }

    /**
     * Grab the tot vehicle auths for both application and licence
     *
     * @param mixed $entity entity
     *
     * @return array
     */
    protected function getTotAuthVehicles(mixed $entity)
    {
        return $entity->getTotAuthVehicles();
    }

    /**
     * Check whether the licence is psv
     *
     * @return boolean
     */
    protected function isPsv()
    {
        return $this->application->isPsv();
    }

    /**
     * Return the section name of the vehicle section based on whether the licence is goods or psv
     *
     * @return string
     */
    protected function getRelevantVehicleSection()
    {
        if ($this->isPsv()) {
            return 'vehicles_psv';
        }

        return 'vehicles';
    }

    /**
     * Check whether the total auth vehicles has dropped below the number of vehicles added
     *
     * @return boolean
     */
    protected function hasTotAuthVehiclesDroppedBelowVehicleCount()
    {
        $totAuthVehicles = $this->getTotAuthVehicles($this->application);

        $totVehicles = $this->countVehicles($this->licence->getLicenceVehicles());

        return $totAuthVehicles < $totVehicles;
    }

    /**
     * Count vehicles
     *
     * @param ArrayCollection $vehicles vehicles
     *
     * @return mixed
     */
    protected function countVehicles($vehicles)
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->isNull('removalDate'));

        return $vehicles->matching($criteria)->count();
    }

    /**
     * Check whether the total auth vehicles has dropped below the number of discs added
     *
     * @return boolean
     */
    protected function hasTotAuthVehiclesDroppedBelowDiscsCount()
    {
        $totAuthVehicles = $this->getTotAuthVehicles($this->application);
        $totDiscs = $this->licence->getPsvDiscsNotCeasedCount();

        return $totAuthVehicles < $totDiscs;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->updateHelper = $container->get('UpdateOperatingCentreHelper');
        $this->variationHelper = $container->get('VariationOperatingCentreHelper');
        $this->financialStandingHelper = $container->get('FinancialStandingHelperService');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
