<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\TmReputeCheck;

use Doctrine\Common\Collections\Criteria;
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
use Laminas\Filter\Word\UnderscoreToCamelCase;
use Laminas\View\Model\ViewModel;

class Generator extends AbstractGenerator
{
    public function __construct(
        AbstractGeneratorServices $abstractGeneratorServices,
    ) {
        parent::__construct($abstractGeneratorServices);
    }

    public function generate(TransportManagerApplication $tma)
    {
    }
}
