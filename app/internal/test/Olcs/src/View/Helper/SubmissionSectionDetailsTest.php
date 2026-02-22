<?php

declare(strict_types=1);

namespace OlcsTest\View\Helper;

use Olcs\View\Helper\SubmissionSectionDetails;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class SubmissionSectionDetails
 * @package OlcsTest\View\Helper
 */
class SubmissionSectionDetailsTest extends TestCase
{
    /**
     * @param $input
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideInvoke')]
    public function testInvoke(mixed $input, mixed $expected): void
    {
        $sut = new SubmissionSectionDetails();

        $mockView = m::mock(\Laminas\View\Renderer\PhpRenderer::class);

        $mockViewHelper = m::mock('Olcs\View\Helper\SubmissionSectionOverview[__invoke]');

        $mockViewHelper->shouldReceive('__invoke');
        $mockView->shouldReceive('plugin')->andReturn($mockViewHelper);

        $sut->setView($mockView);

        $this->assertEquals(
            $expected,
            $sut(
                $input['submissionSection'],
                $input['data']
            )
        );
    }

    /**
     * @param $input
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideInvokeNotPluggable')]
    public function testInvokeNotPluggable(mixed $input, mixed $expected): void
    {
        $sut = new SubmissionSectionDetails();

        $mockView = m::mock(\Laminas\View\Renderer\RendererInterface::class);

        $sut->setView($mockView);

        $this->assertEquals(
            $expected,
            $sut(
                $input['submissionSection'],
                $input['data']
            )
        );
    }

    public static function provideInvoke(): array
    {
        return [
            [['submissionSection' => '', 'data' => []], ''],
            [['submissionSection' => [], 'data' => []], ''],
            [['submissionSection' => null, 'data' => []], ''],
            [['submissionSection' => false, 'data' => []], ''],
            [['submissionSection' => 'rubbish', 'data' => []], ''],
            [['submissionSection' => 'introduction', 'data' => []], ''],
            [['submissionSection' => 'penalties', 'data' => []], ''],

        ];
    }

    public static function provideInvokeNotPluggable(): array
    {
        return [
            [['submissionSection' => 'rubbish', 'data' => []], ''],
            [['submissionSection' => 'submission_section_intr', 'data' => []], '']
        ];
    }
}
