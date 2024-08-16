<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview;

use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Lva\SectionAccessService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGenerator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\Filter\Word\UnderscoreToCamelCase;

/**
 * Continuation Review generator
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Generator extends AbstractGenerator
{
    public const PEOPLE_SECTION = 'people';
    public const TRAILERS_SECTION = 'trailers';
    public const TAXI_PHV_SECTION = 'taxi_phv';
    public const DISCS_SECTION = 'discs';
    public const COMMUNITY_LICENCES_SECTION = 'community_licences';
    public const FINANCE_SECTION = 'finance';
    public const DECLARATION_SECTION = 'declaration';
    public const CONDITIONS_UNDERTAKINGS_SECTION = 'conditions_undertakings';
    public const OPERATING_CENTRES_SECTION = 'operating_centres';

    /**
     * Create service instance
     *
     * container being passed into the constructor
     *
     * @param AbstractGeneratorServices $abstractGeneratorServices
     * @param SectionAccessService $sectionAccessService
     * @param NiTextTranslation $niTextTranslation
     * @param ContainerInterface $services
     *
     * @return Generator
     */
    public function __construct(
        AbstractGeneratorServices $abstractGeneratorServices,
        private readonly SectionAccessService $sectionAccessService,
        private readonly NiTextTranslation $niTextTranslation,
        private readonly ContainerInterface $services
    ) {
        parent::__construct($abstractGeneratorServices);
    }

    /**
     * Generate
     *
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return string
     */
    public function generate(ContinuationDetail $continuationDetail)
    {
        $licence = $continuationDetail->getLicence();

        $sections = $this->sectionAccessService->getAccessibleSectionsForLicenceContinuation($licence);
        $sections = $this->alterSections(array_keys($sections), $licence);

        $this->niTextTranslation->setLocaleForNiFlag($licence->getNiFlag());

        $config = $this->buildReadonlyConfigForSections($sections, $continuationDetail);

        return $this->generateReadonly($config, 'continuation-review');
    }

    /**
     * Build readonly config for sections
     *
     * @param array              $sections           sections
     * @param ContinuationDetail $continuationDetail continuation details
     *
     * @return array
     */
    protected function buildReadonlyConfigForSections(array $sections, ContinuationDetail $continuationDetail)
    {
        $filter = new UnderscoreToCamelCase();

        $sectionConfig = [];

        foreach ($sections as $section) {
            $serviceName = 'ContinuationReview\\' . ucfirst((string) $filter->filter($section));
            $config = null;
            $summary = null;
            $summaryHeader = null;

            // @NOTE this check is in place while we implement each section
            // eventually we should be able to remove the if
            if ($this->services->has($serviceName)) {
                $service = $this->services->get($serviceName);
                $config = $service->getConfigFromData($continuationDetail);
                if (method_exists($service, 'getSummaryFromData')) {
                    $summary = $service->getSummaryFromData($continuationDetail);
                }
                if (method_exists($service, 'getSummaryHeader')) {
                    $summaryHeader = $service->getSummaryHeader($continuationDetail);
                }
            }

            $sectionConfig[] = [
                'header' => $this->getSectionHeader($section, $continuationDetail),
                'config' => $config
            ];
            if ($summary !== null) {
                $sectionConfig[count($sectionConfig) - 1]['summary'] = $summary;
            }
            if ($summaryHeader !== null) {
                $sectionConfig[count($sectionConfig) - 1]['summaryHeader'] = $summaryHeader;
            }
        }

        return [
            'reviewTitle' => $this->getTitle($continuationDetail),
            'subTitle' => 'continuation-review-subtitle',
            'sections' => $sectionConfig
        ];
    }

    /**
     * Get title
     *
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return string
     */
    protected function getTitle(ContinuationDetail $continuationDetail)
    {
        return sprintf(
            '%s %s',
            $continuationDetail->getLicence()->getOrganisation()->getName(),
            $continuationDetail->getLicence()->getLicNo()
        );
    }

    /**
     * Get section header
     *
     * @param string             $section            section
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return string
     */
    protected function getSectionHeader($section, ContinuationDetail $continuationDetail)
    {
        $header = 'continuation-review-' . $section;

        if ($section === self::PEOPLE_SECTION) {
            $header .= '-' . $continuationDetail->getLicence()->getOrganisation()->getType()->getId();
        } elseif ($section == self::OPERATING_CENTRES_SECTION) {
            if ($continuationDetail->getLicence()->getVehicleType()->getId() == RefData::APP_VEHICLE_TYPE_LGV) {
                $header .= '.lgv';
            }
        }

        return $header;
    }

    /**
     * Alter sections
     *
     * @param array   $sections sections
     * @param Licence $licence  licence
     *
     * @return array
     */
    protected function alterSections(array $sections, Licence $licence)
    {
        $sectionsToRemove = [
            self::TRAILERS_SECTION,
            self::TAXI_PHV_SECTION,
            self::DISCS_SECTION,
            self::COMMUNITY_LICENCES_SECTION
        ];
        foreach ($sectionsToRemove as $sectionToRemove) {
            if (in_array($sectionToRemove, $sections)) {
                unset($sections[array_search($sectionToRemove, $sections)]);
            }
        }

        if ($licence->getLicenceType()->getId() !== Licence::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            $sections[] = self::FINANCE_SECTION;
        }

        if (
            count($licence->getConditionUndertakings()) === 0
            && in_array(self::CONDITIONS_UNDERTAKINGS_SECTION, $sections)
        ) {
            unset($sections[array_search(self::CONDITIONS_UNDERTAKINGS_SECTION, $sections)]);
        }

        if ($licence->isRestricted() && $licence->isPsv()) {
            $sections[] = self::CONDITIONS_UNDERTAKINGS_SECTION;
        }

        $sections[] = self::DECLARATION_SECTION;

        return $sections;
    }
}
