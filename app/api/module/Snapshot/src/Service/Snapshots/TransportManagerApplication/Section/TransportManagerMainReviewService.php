<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;

/**
 * Transport Manager Main Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerMainReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param TransportManagerApplication $tma Transport Manager Application Entity
     *
     * @return array
     */
    public function getConfig(TransportManagerApplication $tma)
    {
        $tm = $tma->getTransportManager();

        $workContactDetails = $tm->getWorkCd();
        $contactDetails = $tm->getHomeCd();

        $person = $contactDetails->getPerson();

        return [
            'multiItems' => [
                [
                    [
                        'label' => 'tm-review-main-name',
                        'value' => $this->formatPersonFullName($person)
                    ],
                    [
                        'label' => 'tm-review-main-birthDate',
                        'value' => $this->formatDate($person->getBirthDate())
                    ],
                    [
                        'label' => 'tm-review-main-birthPlace',
                        'value' => $person->getBirthPlace()
                    ],
                    [
                        'label' => 'tm-review-main-email',
                        'value' => $contactDetails->getEmailAddress()
                    ],
                    [
                        'label' => 'tm-review-main-certificate',
                        'noEscape' => true,
                        'value' => $this->formatCertificateFiles($tma)
                    ],
                    [
                        'label' => 'tm-review-responsibility-training-undertaken',
                        'value' => $this->formatYesNo($tma->getHasUndertakenTraining()),
                    ],
                    [
                        'label' => 'tm-review-main-home-address',
                        'value' => $contactDetails->getAddress() ?
                            $this->formatFullAddress($contactDetails->getAddress()) : ''
                    ],
                    [
                        'label' => 'tm-review-main-work-address',
                        'value' => $workContactDetails && $workContactDetails->getAddress() ?
                            $this->formatFullAddress($workContactDetails->getAddress()) : ''
                    ]
                ]
            ]
        ];
    }

    /**
     * Get files and format output
     *
     * @param TransportManagerApplication $tma TMA Entity
     *
     * @return string
     */
    private function formatCertificateFiles(TransportManagerApplication $tma)
    {
        /** @var \Doctrine\Common\Collections\ArrayCollection $files */
        $files = $this->findFiles(
            $tma->getTransportManager()->getDocuments(),
            Category::CATEGORY_TRANSPORT_MANAGER,
            Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION
        );

        if ($files->isEmpty()) {
            return $this->translate('tm-review-main-no-files');
        }

        $fileNames = [];

        /** @var \Dvsa\Olcs\Api\Entity\Doc\Document $file */
        foreach ($files as $file) {
            $fileNames[] = $file->getDescription();
        }

        return implode('<br>', $fileNames);
    }
}
