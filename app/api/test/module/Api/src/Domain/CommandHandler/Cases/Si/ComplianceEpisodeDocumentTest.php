<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Si;

use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si\ComplianceEpisodeDocument;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Si\ComplianceEpisode as ComplianceEpisodeDocCmd;
use Dvsa\Olcs\Api\Domain\Command\Cases\Si\ComplianceEpisode as ComplianceEpisodeProcessCmd;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;

/**
 * ComplianceEpisodeDocumentTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ComplianceEpisodeDocumentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ComplianceEpisodeDocument();

        parent::setUp();
    }

    /**
     * Tests a successful compliance episode
     */
    public function testHandleCommand(): void
    {
        $xmlString = 'xml string';
        $documentId = 123;
        $cmd = ComplianceEpisodeDocCmd::create(['xml' => $xmlString]);

        $this->documentSideEffect($xmlString, $documentId);
        $this->complianceEpisodeSideEffect($documentId, false);

        $this->assertInstanceOf(Result::class, $this->sut->handleCommand($cmd));
    }

    /**
     * tests correct exception is thrown when errors are returned
     */
    public function testHandleCommandWithException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\Exception::class);

        $xmlString = 'xml string';
        $documentId = 123;
        $cmd = ComplianceEpisodeDocCmd::create(['xml' => $xmlString]);

        $this->documentSideEffect($xmlString, $documentId);
        $this->complianceEpisodeSideEffect($documentId, true);

        $this->assertInstanceOf(Result::class, $this->sut->handleCommand($cmd));
    }

    /**
     * Gets document upload data
     *
     * @param string $xmlString   xml string
     * @param int    $documentId  document id
     *
     * @return UploadCmd
     */
    private function documentSideEffect(mixed $xmlString, mixed $documentId): void
    {
        $documentData = [
            'content' => base64_encode($xmlString),
            'category' => CategoryEntity::CATEGORY_COMPLIANCE,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_NR,
            'filename' => 'compliance-episode.xml',
            'description' => 'ERRU incoming compliance episode',
            'isExternal' => true
        ];

        $documentResult = new Result();
        $documentResult->addId('document', $documentId);
        $this->expectedSideEffect(UploadCmd::class, $documentData, $documentResult);
    }

    /**
     * Gets a compliance episode side effect
     *
     * @param int  $documentId document id
     * @param bool $hasErrors  whether there were errors
     *
     * @return ComplianceEpisodeProcessCmd
     */
    private function complianceEpisodeSideEffect(mixed $documentId, mixed $hasErrors): void
    {
        $dtoData = ['id' => $documentId];

        $complianceEpisodeResult = new Result();
        $complianceEpisodeResult->setFlag('hasErrors', $hasErrors);

        $this->expectedSideEffect(ComplianceEpisodeProcessCmd::class, $dtoData, $complianceEpisodeResult);
    }
}
