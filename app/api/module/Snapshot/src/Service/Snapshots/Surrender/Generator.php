<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGenerator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\CommunityLicenceReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\CurrentDiscsReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\DeclarationReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\OperatorLicenceReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\LicenceDetailsService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\SignatureReviewService;

class Generator extends AbstractGenerator
{
    /**
     * Create service instance
     *
     * @param AbstractGeneratorServices $abstractGeneratorServices
     * @param LicenceDetailsService $licenceDetailsService
     * @param CurrentDiscsReviewService $currentDiscsReviewService
     * @param OperatorLicenceReviewService $operatorLicenceReviewService
     * @param CommunityLicenceReviewService $communityLicenceReviewService
     * @param DeclarationReviewService $declarationReviewService
     * @param SignatureReviewService $signatureReviewService
     *
     * @return Generator
     */
    public function __construct(
        AbstractGeneratorServices $abstractGeneratorServices,
        private readonly LicenceDetailsService $licenceDetailsService,
        private readonly CurrentDiscsReviewService $currentDiscsReviewService,
        private readonly OperatorLicenceReviewService $operatorLicenceReviewService,
        private readonly CommunityLicenceReviewService $communityLicenceReviewService,
        private readonly DeclarationReviewService $declarationReviewService,
        private readonly SignatureReviewService $signatureReviewService
    ) {
        parent::__construct($abstractGeneratorServices);
    }

    public function generate(Surrender $surrender)
    {
        $sections = [
            $this->getLicenceDetailsSection($surrender),
            $this->getCurrentDiscsSection($surrender),
            $this->getOperatorLicenceDocumentationSection($surrender),
            $this->getDeclarationSection($surrender),
            $this->getSignatureSection($surrender)
        ];

        if ($surrender->getLicence()->getLicenceType()->getId() === Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL) {
            array_splice($sections, 3, 0, [$this->getCommunityLicenceSection($surrender)]);
        }
        return $this->generateReadonly(
            [
                'reviewTitle' => 'surrender-review-title',
                'subTitle' => $surrender->getLicence()->getLicNo(),
                'settings' => [
                    'hide-count' => true
                ],
                'sections' => $sections
            ]
        );
    }

    protected function getLicenceDetailsSection(Surrender $surrender)
    {
        return [
            'header' => 'surrender-review-licence',
            'config' => $this->licenceDetailsService->getConfigFromData($surrender)
        ];
    }

    protected function getCurrentDiscsSection(Surrender $surrender)
    {
        return [
            'header' => 'surrender-review-current-discs',
            'config' => $this->currentDiscsReviewService->getConfigFromData($surrender)
        ];
    }

    protected function getOperatorLicenceDocumentationSection(Surrender $surrender)
    {
        return [
            'header' => 'surrender-review-operator-licence',
            'config' => $this->operatorLicenceReviewService->getConfigFromData($surrender)
        ];
    }

    protected function getCommunityLicenceSection(Surrender $surrender)
    {
        return [
            'header' => 'surrender-review-community-licence',
            'config' => $this->communityLicenceReviewService->getConfigFromData($surrender)
        ];
    }


    protected function getDeclarationSection(Surrender $surrender)
    {
        return [
            'header' => 'surrender-review-declaration',
            'config' => $this->declarationReviewService->getConfigFromData($surrender)
        ];
    }

    protected function getSignatureSection(Surrender $surrender)
    {
        return [
            'config' => $this->signatureReviewService->getConfigFromData($surrender)
        ];
    }
}
