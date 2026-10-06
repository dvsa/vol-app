<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\DocumentAnalysisBelongsToApplication;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis;
use Mockery as m;

final class DocumentAnalysisBelongsToApplicationTest extends AbstractValidatorsTestCase
{
    /**
     * @var DocumentAnalysisBelongsToApplication
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new DocumentAnalysisBelongsToApplication();

        parent::setUp();
    }

    public function testIsValidMatchingId(): void
    {
        $this->assertTrue($this->sut->isValid($this->analysisOnApplication(5), 5));
    }

    public function testIsValidMismatchedId(): void
    {
        $this->assertFalse($this->sut->isValid($this->analysisOnApplication(5), 999));
    }

    /** The application is nulled when it is deleted; such an analysis belongs to no application. */
    public function testIsValidAnalysisHasNoApplication(): void
    {
        $analysis = m::mock(DocumentAnalysis::class);
        $analysis->expects('getApplication')->withNoArgs()->andReturnNull();

        $this->assertFalse($this->sut->isValid($analysis, 5));
    }

    public function testIsValidFetchesTheAnalysisById(): void
    {
        $this->mockRepo('DocumentAnalysis')
            ->expects('fetchById')
            ->with(7)
            ->andReturn($this->analysisOnApplication(5));

        $this->assertTrue($this->sut->isValid(7, 5));
    }

    private function analysisOnApplication(int $applicationId): DocumentAnalysis|m\MockInterface
    {
        $application = m::mock(Application::class);
        $application->allows('getId')->andReturn($applicationId);

        $analysis = m::mock(DocumentAnalysis::class);
        $analysis->allows('getApplication')->andReturn($application);

        return $analysis;
    }
}

