<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\DocumentAnalysisBelongsToLicence;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Mirrors the licence scope of DocumentAnalysisList: the document's own licence, or the
 * licence of the analysis' application.
 */
final class DocumentAnalysisBelongsToLicenceTest extends AbstractValidatorsTestCase
{
    /**
     * @var DocumentAnalysisBelongsToLicence
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new DocumentAnalysisBelongsToLicence();

        parent::setUp();
    }

    public static function provider(): \Iterator
    {
        yield 'document on the licence' => [5, null, 5, true];
        yield 'application on the licence' => [null, 5, 5, true];
        yield 'document on the licence, application on another' => [5, 6, 5, true];
        yield 'application on the licence, document on another' => [6, 5, 5, true];
        yield 'neither on the licence' => [6, 7, 5, false];
        yield 'no licence anywhere' => [null, null, 5, false];
    }

    #[DataProvider('provider')]
    public function testIsValid(?int $documentLicenceId, ?int $applicationLicenceId, int $licenceId, bool $expected): void
    {
        $this->assertSame(
            $expected,
            $this->sut->isValid($this->analysis($documentLicenceId, $applicationLicenceId), $licenceId)
        );
    }

    /** A deleted application leaves the analysis with no application at all. */
    public function testIsValidWithNoApplication(): void
    {
        $licence = m::mock(Licence::class);
        $licence->allows('getId')->andReturn(5);

        $document = m::mock(Document::class);
        $document->allows('getLicence')->andReturn($licence);

        $analysis = m::mock(DocumentAnalysis::class);
        $analysis->allows('getDocument')->andReturn($document);
        $analysis->allows('getApplication')->andReturnNull();

        $this->assertTrue($this->sut->isValid($analysis, 5));
        $this->assertFalse($this->sut->isValid($analysis, 6));
    }

    public function testIsValidWithNoParent(): void
    {
        $this->assertFalse($this->sut->isValid($this->analysis(5, 5), null));
    }

    public function testIsValidFetchesTheAnalysisById(): void
    {
        $this->mockRepo('DocumentAnalysis')
            ->expects('fetchById')
            ->with(7)
            ->andReturn($this->analysis(5, null));

        $this->assertTrue($this->sut->isValid(7, 5));
    }

    private function analysis(?int $documentLicenceId, ?int $applicationLicenceId): DocumentAnalysis|m\MockInterface
    {
        $document = m::mock(Document::class);
        $document->allows('getLicence')->andReturn($this->licence($documentLicenceId));

        $application = m::mock(Application::class);
        $application->allows('getLicence')->andReturn($this->licence($applicationLicenceId));

        $analysis = m::mock(DocumentAnalysis::class);
        $analysis->allows('getDocument')->andReturn($document);
        $analysis->allows('getApplication')->andReturn($application);

        return $analysis;
    }

    private function licence(?int $id): ?Licence
    {
        if ($id === null) {
            return null;
        }

        $licence = m::mock(Licence::class);
        $licence->allows('getId')->andReturn($id);

        return $licence;
    }
}

