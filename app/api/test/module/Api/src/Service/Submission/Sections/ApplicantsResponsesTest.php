<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Service\Submission\Sections\ApplicantsResponses;
use Laminas\View\Renderer\PhpRenderer;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;

class ApplicantsResponsesTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = ApplicantsResponses::class;

    public static function sectionTestProvider(): array
    {
        $case = static::getCase();

        $expectedResult = 'foo';

        return [
            [$case, $expectedResult],
        ];
    }

    /**
     *
     * @param $section
     * @param $expectedString
     */
    #[DataProvider('sectionTestProvider')]
    #[\Override]
    public function testGenerateSection(mixed $input = null, mixed $expectedResult = null): void
    {
        $mockQueryHandler = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class);
        $mockViewRenderer = m::mock(PhpRenderer::class);

        $mockViewRenderer->shouldReceive('render')
            ->once()
            ->with('/sections/applicants-responses.phtml')
            ->andReturn('foo');

        $sut = new $this->submissionSection($mockQueryHandler, $mockViewRenderer);

        $result = $sut->generateSection($input);

        $this->assertArrayHasKey('text', $result['data']);
        $this->assertEquals($result['data']['text'], 'foo');
    }
}
