<?php

namespace OlcsTest\View\Helper;

use Olcs\View\Helper\SubmissionSectionOverview;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class SubmissionSectionOverview
 * @package OlcsTest\View\Helper
 */
class SubmissionSectionOverviewTest extends TestCase
{
    /**
     * @param $input
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideInvoke')]
    public function testInvoke($input, $expected)
    {
        $sut = new SubmissionSectionOverview();

        $mockView = m::mock(\Laminas\View\Renderer\PhpRenderer::class);

        $mockViewHelper = m::mock(\Olcs\View\Helper\SubmissionSectionOverview::class);

        $mockViewHelper->shouldReceive('__invoke');
        $mockView->shouldReceive('plugin')->andReturn($mockViewHelper);
        $mockView->shouldReceive('render');

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
    public function testInvokeNotPluggable($input, $expected)
    {
        $sut = new SubmissionSectionOverview();

        $mockView = m::mock(\Laminas\View\Renderer\RendererInterface::class);

        $mockView->shouldReceive('render');

        $sut->setView($mockView);

        $this->assertEquals(
            $expected,
            $sut(
                $input['submissionSection'],
                $input['data']
            )
        );
    }

    public static function provideInvoke()
    {
        return [
            [['submissionSection' => '', 'data' => []], ''],
            [['submissionSection' => [], 'data' => []], ''],
            [['submissionSection' => null, 'data' => []], ''],
            [['submissionSection' => false, 'data' => []], ''],
            [['submissionSection' => 'rubbish', 'data' => []], ''],
            [['submissionSection' => 'introduction', 'data' => []], ''],

        ];
    }

    public static function provideInvokeNotPluggable()
    {
        return [
            [['submissionSection' => 'rubbish', 'data' => []], ''],
            [['submissionSection' => 'submission_section_intr', 'data' => []], '']
        ];
    }
}
