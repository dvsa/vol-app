<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Laminas\View\Renderer\PhpRenderer;
use Mockery as m;

/**
 * Class ApplicantsResponsesTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class ApplicantsResponsesTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\ApplicantsResponses::class;

    /**
     * Filter provider
     *
     * @return array
     */
    public function sectionTestProvider()
    {
        $case = $this->getCase();

        $expectedResult = 'foo';

        return [
            [$case, $expectedResult],
        ];
    }

    /**
     * @dataProvider sectionTestProvider
     *
     * @param $section
     * @param $expectedString
     */
    public function testGenerateSection($input = null, $expectedResult = null)
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
