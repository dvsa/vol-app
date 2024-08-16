<?php

/**
 * Application Review
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Service\Lva\SectionAccessService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGenerator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\SignatureReviewService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\Filter\Word\UnderscoreToCamelCase;

/**
 * Application Review
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Generator extends AbstractGenerator
{
    protected $defaultBundle = [
        'licence' => [
            'organisation' => ['type']
        ]
    ];

    protected $sharedBundles = [
        'transport_managers' => [
            'transportManagers' => [
                'transportManager' => [
                    'homeCd' => [
                        'person' => [
                            'title'
                        ]
                    ]
                ]
            ]
        ],
        'operating_centres' => [
            'licence' => [
                'trafficArea'
            ],
            'operatingCentres' => [
                'application',
                'operatingCentre' => [
                    'address',
                    'adDocuments' => [
                        'application'
                    ]
                ]
            ]
        ],
        'vehicles' => [
            'licenceVehicles' => [
                'vehicle'
            ]
        ],
        'vehicles_psv' => [
            'licenceVehicles' => [
                'vehicle'
            ]
        ],
        'convictions_penalties' => [
            'previousConvictions' => [
                'title'
            ]
        ],
        'licence_history' => [
            'otherLicences' => [
                'previousLicenceType'
            ]
        ],
        'financial_history' => [
            'documents' => [
                'category',
                'subCategory'
            ]
        ],
        'conditions_undertakings' => [
            'conditionUndertakings' => [
                'conditionType',
                'attachedTo',
                'operatingCentre' => [
                    'address'
                ]
            ]
        ]
    ];

    protected $applicationBundles = [
        'business_type' => [
            'licence' => [
                'organisation' => [
                    'type'
                ]
            ]
        ],
        'business_details' => [
            'licence' => [
                'companySubsidiaries',
                'organisation' => [
                    'type',
                    'contactDetails' => [
                        'address'
                    ]
                ],
                'tradingNames'
            ]
        ],
        'safety' => [
            'licence' => [
                'workshops' => [
                    'contactDetails' => [
                        'address'
                    ]
                ],
                'tachographIns'
            ]
        ],
        'addresses' => [
            'licence' => [
                'correspondenceCd' => [
                    'address',
                    'phoneContacts' => [
                        'phoneContactType'
                    ]
                ],
                'establishmentCd' => [
                    'address'
                ]
            ]
        ],
        'taxi_phv' => [
            'licence' => [
                'trafficArea',
                'privateHireLicences' => [
                    'contactDetails' => [
                        'address'
                    ]
                ]
            ]
        ],
        'people' => [
            'licence' => [
                'organisation' => [
                    'type',
                    'organisationPersons' => [
                        'person' => [
                            'title'
                        ]
                    ]
                ]
            ],
            'applicationOrganisationPersons' => [
                'originalPerson',
                'person' => [
                    'title'
                ]
            ]
        ],
        'vehicles_declarations' => [
            'licence' => [
                'trafficArea'
            ]
        ]
    ];

    protected $variationBundles = [
        'type_of_licence' => [
            'licence' => [
                'licenceType',
                'vehicleType'
            ]
        ],
        'people' => [
            'licence' => [
                'organisation' => [
                    'type'
                ]
            ],
            'applicationOrganisationPersons' => [
                'person' => [
                    'title'
                ]
            ]
        ],
        'licence_history' => [
            'otherLicences' => [
                'previousLicenceType'
            ]
        ],
        'conditions_undertakings' => [
            'conditionUndertakings' => [
                'licConditionVariation'
            ]
        ]
    ];

    protected $ignoredApplicationSections = [
        'community_licences'
    ];

    protected $ignoredVariationSections = [
        'community_licences'
    ];

    protected $displayedAlwaysVariationSections = [
        'undertakings'
    ];

    protected $sectionMap = [
        'declarations_internal' => 'undertakings'
    ];

    protected $lva;

    /**
     * Create service instance
     *
     * container being passed into the constructor
     *
     * @param AbstractGeneratorServices $abstractGeneratorServices
     * @param SectionAccessService $sectionAccessService
     * @param NiTextTranslation $niTextTranslation
     * @param SignatureReviewService $signatureReviewService
     * @param ContainerInterface $services
     *
     * @return Generator
     */
    public function __construct(
        AbstractGeneratorServices $abstractGeneratorServices,
        private readonly SectionAccessService $sectionAccessService,
        private readonly NiTextTranslation $niTextTranslation,
        private readonly SignatureReviewService $signatureReviewService,
        private readonly ContainerInterface $services
    ) {
        parent::__construct($abstractGeneratorServices);

        $notRemovedCriteria = Criteria::create();
        $notRemovedCriteria->andWhere(
            $notRemovedCriteria->expr()->isNull('removalDate')
        );

        $this->sharedBundles['vehicles']['licenceVehicles']['criteria'] = $notRemovedCriteria;
        $this->sharedBundles['vehicles_psv']['licenceVehicles']['criteria'] = $notRemovedCriteria;
    }

    public function generate(Application $application, $isInternal = true)
    {
        $sections = $this->sectionAccessService->getAccessibleSections($application);
        $sections = array_keys($sections);

        $sections = $this->mapSections($sections);

        // Set the NI Locale
        $this->niTextTranslation->setLocaleForNiFlag($application->getNiFlag());

        if ($application->isVariation()) {
            $this->lva = 'variation';
            $sections = $this->filterVariationSections($sections, $application->getApplicationCompletion());

            $bundle = $this->getReviewDataBundleForVariation($sections);
        } else {
            $this->lva = 'application';
            $sections = $this->filterApplicationSections($sections);

            $bundle = $this->getReviewDataBundleForApplication($sections);
        }

        $result = new Result(
            $application,
            $bundle,
            [
                'sections' => $sections,
                'isGoods' => $application->isGoods(),
                'isSpecialRestricted' => $application->isSpecialRestricted(),
                'isInternal' => $isInternal
            ]
        );

        $data = $result->serialize();

        $config = $this->buildReadonlyConfigForSections($data['sections'], $data);

        if ($this->lva === 'application' && !is_null($application->getSignatureType())) {
            $config['sections'][] = $this->buildSignatureSection($application);
        }

        // Generate readonly markup
        return $this->generateReadonly($config);
    }

    /**
     * Maps sections to their alternative
     *
     * This was added when the internal only version of the undertakings section was added, this method maps the
     * duplicate section for the snapshot
     *
     * @param $sections
     *
     * @return mixed
     */
    protected function mapSections($sections)
    {
        foreach ($sections as $k => $v) {
            if (isset($this->sectionMap[$v])) {
                $sections[$k] = $this->sectionMap[$v];
            }
        }

        return $sections;
    }

    protected function buildReadonlyConfigForSections($sections, $reviewData)
    {
        $entity = ucfirst((string) $this->lva);

        $filter = new UnderscoreToCamelCase();

        $sectionConfig = [];

        foreach ($sections as $section) {
            $serviceName = 'Review\\' . $entity . ucfirst((string) $filter->filter($section));

            $config = null;
            $header = 'review-' . $section;

            // @NOTE this check is in place while we implement each section
            // eventually we should be able to remove the if
            if ($this->services->has($serviceName)) {
                $service = $this->services->get($serviceName);
                $header = $service->getHeaderTranslationKey($reviewData, $section);
                $config = $service->getConfigFromData($reviewData);
            }

            $sectionConfig[] = [
                'header' => $header,
                'config' => $config
            ];
        }

        return [
            'reviewTitle' => $this->getTitle($reviewData),
            'subTitle' => $this->getSubTitle($reviewData),
            'sections' => $sectionConfig
        ];
    }

    protected function getSubTitle($data)
    {
        return sprintf('%s %s/%s', $data['licence']['organisation']['name'], $data['licence']['licNo'], $data['id']);
    }

    protected function getTitle($data)
    {
        return sprintf(
            '%s-review-title-%s%s',
            $this->lva,
            $data['isGoods'] ? 'gv' : 'psv',
            $this->isNewPsvSpecialRestricted($data) ? '-sr' : ''
        );
    }

    protected function isNewPsvSpecialRestricted($data)
    {
        return $this->lva === 'application' && !$data['isGoods'] && $data['isSpecialRestricted'];
    }

    protected function filterVariationSections($sections, ApplicationCompletion $completion)
    {
        $sections = array_values(array_diff($sections, $this->ignoredVariationSections));

        $filter = new UnderscoreToCamelCase();

        foreach ($sections as $key => $section) {
            $getter = 'get' . ucfirst((string) $filter->filter($section)) . 'Status';

            if (
                array_search($section, $this->displayedAlwaysVariationSections) === false &&
                $completion->$getter() !== Application::VARIATION_STATUS_UPDATED
            ) {
                unset($sections[$key]);
            }
        }

        return $sections;
    }

    protected function filterApplicationSections($sections)
    {
        return array_values(array_diff($sections, $this->ignoredApplicationSections));
    }

    protected function getReviewDataBundleForApplication(array $sections = [])
    {
        return $this->getReviewBundle($sections, 'application');
    }

    /**
     * Grab all of the review for a variation
     *
     *
     * @return array
     */
    protected function getReviewDataBundleForVariation(array $sections = [])
    {
        return $this->getReviewBundle($sections, 'variation');
    }

    /**
     * Dynamically build the review bundle
     *
     * @param array  $sections
     * @param string $lva
     *
     * @return array
     */
    protected function getReviewBundle($sections, $lva)
    {
        $bundle = $this->defaultBundle;

        foreach ($sections as $section) {
            if (isset($this->sharedBundles[$section])) {
                $bundle = array_merge_recursive($bundle, $this->sharedBundles[$section]);
            }

            if (isset($this->{$lva . 'Bundles'}[$section])) {
                $bundle = array_merge_recursive($bundle, $this->{$lva . 'Bundles'}[$section]);
            }
        }

        return $bundle;
    }

    /**
     * @return array
     */
    private function buildSignatureSection(Application $application): array
    {
        $data = [
            'organisation' => $application->getLicence()->getOrganisation(),
            'signatureType' => $application->getSignatureType(),
            'digitalSignature' => $application->getDigitalSignature(),
            'isNi' => $application->getLicence()->isNi()
        ];

        return [
            'hide-count' => true,
            'config' => $this->signatureReviewService->getConfigFromData($data)
        ];
    }
}
