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
final class ApplicantsCommentsTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = ApplicantsComments::class;

    public static function sectionTestProvider(): \Iterator
    {
        $expectedResult = 'foo';

        yield [null, $expectedResult];
    }

    #[\Override]
    public function testGenerateSection(mixed $input = null, mixed $expectedResult = null): void
    {
        $input = static::getCase();

        $mockQueryHandler = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class);
        $mockViewRenderer = m::mock(PhpRenderer::class);

        $mockViewRenderer->shouldReceive('render')
            ->once()
            ->with('/sections/applicants-comments.phtml')
            ->andReturn('foo');

        $sut = new $this->submissionSection($mockQueryHandler, $mockViewRenderer);

        $result = $sut->generateSection($input);

        $this->assertArrayHasKey('text', $result['data']);
        $this->assertEquals('foo', $result['data']['text']);
    }
}
