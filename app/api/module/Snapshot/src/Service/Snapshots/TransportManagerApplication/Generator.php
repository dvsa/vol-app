<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication;

use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGenerator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerMainReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerResponsibilityReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerOtherEmploymentReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerPreviousConvictionReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerPreviousLicenceReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerDeclarationReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerSignatureReviewService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;

class Generator extends AbstractGenerator
{
    /**
     * Create service instance
     *
     * @param AbstractGeneratorServices $abstractGeneratorServices
     * @param NiTextTranslation $niTextTranslation
     * @param TransportManagerMainReviewService $transportManagerMainReviewService
     * @param TransportManagerResponsibilityReviewService $transportManagerResponsibilityReviewService
     * @param TransportManagerOtherEmploymentReviewService $transportManagerOtherEmploymentReviewService
     * @param TransportManagerPreviousConvictionReviewService $transportManagerPreviousConvictionReviewService
     * @param TransportManagerPreviousLicenceReviewService $transportManagerPreviousLicenceReviewService
     * @param TransportManagerDeclarationReviewService $transportManagerDeclarationReviewService
     * @param TransportManagerSignatureReviewService $transportManagerSignatureReviewService
     *
     * @return Generator
     */
    public function __construct(
        AbstractGeneratorServices $abstractGeneratorServices,
        private readonly NiTextTranslation $niTextTranslation,
        private readonly TransportManagerMainReviewService $transportManagerMainReviewService,
        private readonly TransportManagerResponsibilityReviewService $transportManagerResponsibilityReviewService,
        private readonly TransportManagerOtherEmploymentReviewService $transportManagerOtherEmploymentReviewService,
        private readonly TransportManagerPreviousConvictionReviewService $transportManagerPreviousConvictionReviewService,
        private readonly TransportManagerPreviousLicenceReviewService $transportManagerPreviousLicenceReviewService,
        private readonly TransportManagerDeclarationReviewService $transportManagerDeclarationReviewService,
        private readonly TransportManagerSignatureReviewService $transportManagerSignatureReviewService
    ) {
        parent::__construct($abstractGeneratorServices);
    }

    public function generate(TransportManagerApplication $tma, $isInternalUser = false)
    {
        $application = $tma->getApplication();
        $licence = $application->getLicence();
        $organisation = $licence->getOrganisation();

        // Set the NI Locale
        $this->niTextTranslation->setLocaleForNiFlag($application->getNiFlag());

        $subTitle = sprintf(
            '%s %s/%s',
            $organisation->getName(),
            $licence->getLicNo(),
            $application->getId()
        );

        $sections = [];

        $sections[] = $this->getMainDetailsReviewSection($tma);
        $sections[] = $this->getResponsibilityDetailsReviewSection($tma);
        $sections[] = $this->getOtherEmploymentDetailsReviewSection($tma);
        $sections[] = $this->getPreviousConvictionDetailsReviewSection($tma);
        $sections[] = $this->getPreviousLicenceDetailsReviewSection($tma);

        // add declaration/signature block if internal user or if TMA status is Op signed
        if (
            $isInternalUser ||
            $tma->getTmApplicationStatus()->getId() === TransportManagerApplication::STATUS_OPERATOR_SIGNED ||
            $tma->getTmApplicationStatus()->getId() === TransportManagerApplication::STATUS_RECEIVED
        ) {
            $sections[] = $this->getDeclarationReviewSection($tma);
            $sections[] = $this->getSignatureReviewSection($tma);
        }

        return $this->generateReadonly(
            [
                'reviewTitle' => 'tm-review-title',
                'subTitle' => $subTitle,
                'settings' => [
                    'hide-count' => true
                ],
                'sections' => $sections
            ]
        );
    }

    protected function getMainDetailsReviewSection(TransportManagerApplication $tma)
    {
        return [
            'header' => 'tm-review-main',
            'config' => $this->transportManagerMainReviewService->getConfig($tma)
        ];
    }

    protected function getResponsibilityDetailsReviewSection(TransportManagerApplication $tma)
    {
        return [
            'header' => 'tm-review-responsibility',
            'config' => $this->transportManagerResponsibilityReviewService->getConfig($tma)
        ];
    }

    protected function getOtherEmploymentDetailsReviewSection(TransportManagerApplication $tma)
    {
        return [
            'header' => 'tm-review-other-employment',
            'config' => $this->transportManagerOtherEmploymentReviewService->getConfig($tma)
        ];
    }

    protected function getPreviousConvictionDetailsReviewSection(TransportManagerApplication $tma)
    {
        return [
            'header' => 'tm-review-previous-conviction',
            'config' => $this->transportManagerPreviousConvictionReviewService->getConfig($tma)
        ];
    }

    protected function getPreviousLicenceDetailsReviewSection(TransportManagerApplication $tma)
    {
        return [
            'header' => 'tm-review-previous-licence',
            'config' => $this->transportManagerPreviousLicenceReviewService->getConfig($tma)
        ];
    }

    protected function getDeclarationReviewSection(TransportManagerApplication $tma)
    {
        return [
            'header' => 'tm-review-declaration',
            'config' => $this->transportManagerDeclarationReviewService->getConfig($tma)
        ];
    }

    protected function getSignatureReviewSection(TransportManagerApplication $tma)
    {
        return [
            'config' => $this->transportManagerSignatureReviewService->getConfig($tma)
        ];
    }
}
