<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;

/**
 * Transport Manager Responsibility Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerResponsibilityReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param TransportManagerApplication $tma transport manager application
     *
     * @return array
     */
    public function getConfig(TransportManagerApplication $tma)
    {
        return [
            'subSections' => [
                [
                    'mainItems' => [
                        [
                            'multiItems' => [
                                [
                                    [
                                        'label' => 'tm-review-responsibility-tmType',
                                        'value' => $tma->getTmType() ? $tma->getTmType()->getDescription() : ''
                                    ],
                                    [
                                        'label' => 'tm-review-responsibility-isOwner',
                                        'value' => $this->formatYesNo($tma->getIsOwner())
                                    ]
                                ],
                            ]
                        ],
                        [
                            'header' => 'tm-review-responsibility-hours-per-week-header',
                            'multiItems' => [
                                [
                                    [
                                        'label' => 'tm-review-responsibility-mon',
                                        'value' => $this->renderHours($tma->getHoursMon())
                                    ],
                                    [
                                        'label' => 'tm-review-responsibility-tue',
                                        'value' => $this->renderHours($tma->getHoursTue())
                                    ],
                                    [
                                        'label' => 'tm-review-responsibility-wed',
                                        'value' => $this->renderHours($tma->getHoursWed())
                                    ],
                                    [
                                        'label' => 'tm-review-responsibility-thu',
                                        'value' => $this->renderHours($tma->getHoursThu())
                                    ],
                                    [
                                        'label' => 'tm-review-responsibility-fri',
                                        'value' => $this->renderHours($tma->getHoursFri())
                                    ],
                                    [
                                        'label' => 'tm-review-responsibility-sat',
                                        'value' => $this->renderHours($tma->getHoursSat())
                                    ],
                                    [
                                        'label' => 'tm-review-responsibility-sun',
                                        'value' => $this->renderHours($tma->getHoursSun())
                                    ]
                                ],
                            ]
                        ],
                    ]
                ],
                [
                    'title' => 'tm-review-responsibility-other-licences',
                    'mainItems' => $this->formatOtherLicences($tma)
                ],
                [
                    'title' => 'tm-review-responsibility-additional-info-header',
                    'mainItems' => [
                        [
                            'multiItems' => [
                                [
                                    [
                                        'label' => 'tm-review-responsibility-additional-info',
                                        'value' => $tma->getAdditionalInformation()
                                    ],
                                    [
                                        'label' => 'tm-review-responsibility-additional-info-files',
                                        'noEscape' => true,
                                        'value' => $this->formatFiles($tma)
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Render hours
     *
     * @param string $value value
     *
     * @return string
     */
    protected function renderHours($value)
    {
        $hours = $this->translate('hours');

        return (float)$value . ' ' . $hours;
    }

    /**
     * Format files
     *
     * @param TransportManagerApplication $tma transport manager application
     *
     * @return string
     */
    private function formatFiles(TransportManagerApplication $tma)
    {
        /** @var \Doctrine\Common\Collections\ArrayCollection $files */
        $files = $this->findFiles(
            $tma->getTransportManager()->getDocuments(),
            Category::CATEGORY_TRANSPORT_MANAGER,
            Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
        );

        if ($files->isEmpty()) {
            return $this->translate('tm-review-responsibility-no-files');
        }

        $fileNames = [];

        /** @var \Dvsa\Olcs\Api\Entity\Doc\Document $file */
        foreach ($files as $file) {
            $fileNames[] = $file->getDescription();
        }

        return implode('<br>', $fileNames);
    }

    /**
     * Format other licences
     *
     * @param TransportManagerApplication $tma transport manager application
     *
     * @return array
     */
    private function formatOtherLicences(TransportManagerApplication $tma)
    {
        if ($tma->getOtherLicences()->isEmpty()) {
            return [
                [
                    'freetext' => $this->translate('tm-review-responsibility-other-licences-none-added')
                ]
            ];
        }

        $mainItems = [];

        /** @var OtherLicence $otherLicence */
        foreach ($tma->getOtherLicences() as $otherLicence) {
            $role = $otherLicence->getRole();
            $mainItems[] = [
                'header' => $otherLicence->getLicNo(),
                'multiItems' => [
                    [
                        [
                            'label' => 'tm-review-responsibility-other-licences-role',
                            'value' => (
                                $role !== null
                                ? $role->getDescription()
                                : $this->translate('tm-review-responsibility-other-licences-role-not-given')
                            )
                        ],
                        [
                            'label' => 'tm-review-responsibility-other-licences-operating-centres',
                            'value' => $otherLicence->getOperatingCentres()
                        ],
                        [
                            'label' => 'tm-review-responsibility-other-licences-vehicles',
                            'value' => $otherLicence->getTotalAuthVehicles()
                        ],
                        [
                            'label' => 'tm-review-responsibility-other-licences-hours-per-week',
                            'value' => $otherLicence->getHoursPerWeek()
                        ]
                    ]
                ]
            ];
        }

        return $mainItems;
    }
}
