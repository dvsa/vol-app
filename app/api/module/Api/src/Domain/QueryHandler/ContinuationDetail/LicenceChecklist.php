<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\ContinuationDetail;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Laminas\Filter\Word\UnderscoreToCamelCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Licence Checklist for continuation
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceChecklist extends AbstractQueryHandler
{
    protected $repoServiceName = 'ContinuationDetail';

    protected $extraRepos = ['ConditionUndertaking'];

    private const string CONDITIONS_UNDERTAKINGS_SECTION = 'conditions_undertakings';

    /**
     * @var \Dvsa\Olcs\Api\Service\Lva\SectionAccessService
     */
    protected $sectionAccessService;

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var ContinuationDetailEntity $continuationDetail */
        $continuationDetail = $this->getRepo()->fetchWithLicence($query->getId());
        $licence = $continuationDetail->getLicence();
        $notRemoved = Criteria::create();
        $notRemoved->andWhere($notRemoved->expr()->isNull('removalDate'));

        $sections = $this->sectionAccessService->getAccessibleSectionsForLicenceContinuation($licence);
        $sections = $this->alterSections(array_keys($sections), $licence);

        $conditionsUndertakings = $this->getRepo('ConditionUndertaking')
            ->fetchListForLicenceReadOnly($licence->getId());

        return $this->result(
            $continuationDetail,
            [
                'licence' => [
                    'licenceType',
                    'status',
                    'goodsOrPsv',
                    'trafficArea',
                    'organisation' => [
                        'type',
                        'organisationPersons' => [
                            'person' => [
                                'title'
                            ]
                        ],
                        'organisationUsers' => [
                            'user' => [
                                'contactDetails' => [
                                    'person'
                                ],
                                'roles'
                            ],
                        ]
                    ],
                    'tradingNames',
                    'licenceVehicles' => [
                        'vehicle',
                        'criteria' => $notRemoved
                    ],
                    'correspondenceCd' => [
                        'address',
                        'phoneContacts' => [
                            'phoneContactType',
                        ],
                    ],
                    'establishmentCd' => [
                        'address',
                    ],
                    'operatingCentres' => [
                        'operatingCentre' => [
                            'address'
                        ]
                    ],
                    'tmLicences' => [
                        'transportManager' => [
                            'homeCd' => [
                                'person' => [
                                    'title'
                                ]
                            ]
                        ]
                    ],
                    'workshops' => [
                        'contactDetails' => [
                            'person' => [
                                'title'
                            ],
                            'address'
                        ]
                    ],
                    'tachographIns'
                ]
            ],
            [
                'sections' => $sections,
                'ocChanges' => $licence->getOcPendingChanges(),
                'tmChanges' => $licence->getTmPendingChanges(),
                'hasConditionsUndertakings' => count($conditionsUndertakings) > 0,
                'canHaveTrailers' => $licence->canHaveTrailer(),
                'applicableAuthProperties' => $licence->getApplicableAuthProperties(),
                'isMixedWithLgv' => $licence->isVehicleTypeMixedWithLgv(),
            ]
        );
    }

    /**
     * Alter sections
     *
     * @param array   $sections sections
     * @param Licence $licence licence
     *
     * @return array
     */
    protected function alterSections($sections, Licence $licence)
    {
        if (
            count($licence->getConditionUndertakings()) === 0
            && in_array(self::CONDITIONS_UNDERTAKINGS_SECTION, $sections)
        ) {
            unset($sections[array_search(self::CONDITIONS_UNDERTAKINGS_SECTION, $sections)]);
        }
        $filter = new UnderscoreToCamelCase();
        array_walk(
            $sections,
            function (&$item) use ($filter) {
                $item = lcfirst((string) $filter->filter($item));
            }
        );
        return $sections;
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return LicenceChecklist
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->sectionAccessService = $container->get('SectionAccessService');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
