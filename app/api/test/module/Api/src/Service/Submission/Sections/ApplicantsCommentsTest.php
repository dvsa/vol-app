<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Service\Submission\Sections\ApplicantsComments;
use Laminas\View\Renderer\PhpRenderer;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Class ApplicantsCommentsTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class ApplicantsCommentsTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = ApplicantsComments::class;

    public function sectionTestProvider(): array
    {
        $case = $this->getCase();

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
    public function testGenerateSection($input = null, $expectedResult = null)
    {
        $mockQueryHandler = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class);
        $mockViewRenderer = m::mock(PhpRenderer::class);

        $mockViewRenderer->shouldReceive('render')
            ->once()
            ->with('/sections/applicants-comments.phtml')
            ->andReturn('foo');

        $sut = new $this->submissionSection($mockQueryHandler, $mockViewRenderer);

        $result = $sut->generateSection($input);

        $this->assertArrayHasKey('text', $result['data']);
        $this->assertEquals($result['data']['text'], 'foo');
    }
}
