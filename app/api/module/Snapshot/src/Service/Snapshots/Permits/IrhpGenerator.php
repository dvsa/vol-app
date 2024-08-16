<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Permits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryGenerator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGenerator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\SnapshotGeneratorInterface;

/**
 * Class IrhpGenerator
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class IrhpGenerator extends AbstractGenerator implements SnapshotGeneratorInterface
{
    /** @var array */
    private $data;

    /**
     * Create service instance
     *
     * @param AbstractGeneratorServices $abstractGeneratorServices
     * @param AnswersSummaryGenerator $answersSummaryGenerator
     *
     * @return IrhpGenerator
     */
    public function __construct(
        AbstractGeneratorServices $abstractGeneratorServices,
        private readonly AnswersSummaryGenerator $answersSummaryGenerator
    ) {
        parent::__construct($abstractGeneratorServices);
    }

    /**
     * Generate the snapshot html
     *
     * @return string
     *
     * @throws \Exception
     */
    public function generate(): string
    {
        /** @var IrhpApplication $irhpApplication */
        $irhpApplication = $this->data['entity'];

        if (!$irhpApplication instanceof IrhpApplication) {
            throw new \Exception('Snapshot generator expects IRHP application record');
        }

        $permitType = $irhpApplication->getIrhpPermitType();

        $answersSummaryRepresentation = $this->answersSummaryGenerator->generate($irhpApplication, true)
            ->getRepresentation();

        return $this->generateReadonly(
            [
                'permitType' => $permitType->getName()->getDescription(),
                'operator' => $irhpApplication->getLicence()->getOrganisation()->getName(),
                'ref' => $irhpApplication->getApplicationRef(),
                'questionAnswerPartialName' => 'question-answer-section-qa',
                'questionAnswerData' => $answersSummaryRepresentation['rows'],
                'guidanceDeclaration' => [
                    'title' => $permitType->isCertificateOfRoadworthiness()
                        ? 'permits.snapshot.declaration.title.certificate'
                        : 'permits.snapshot.declaration.title',
                    'bullets' => 'markup-irhp-declaration-' . $permitType->getId(),
                    'declaration' => 'permits.snapshot.declaration',
                ],
            ],
            'permit-application'
        );
    }

    /**
     * Set the data needed to generate the HTML
     *
     * @param array $data data required to generate the HTML
     *
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
