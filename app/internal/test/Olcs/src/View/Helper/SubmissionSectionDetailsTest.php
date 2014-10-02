<?php


namespace OlcsTest\View\Helper;

use Olcs\View\Helper\SubmissionSectionDetails;
use Mockery as m;

/**
 * Class SubmissionSectionDetails
 * @package OlcsTest\View\Helper
 */
class SubmissionSectionDetailsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {

    }

    /**
     * @dataProvider provideInvoke
     * @param $input
     * @param $expected
     */
    public function testInvoke($input, $expected)
    {
        $sut = new SubmissionSectionDetails();

        $mockView = m::mock('\Zend\View\Renderer\PhpRenderer');

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
     * @dataProvider provideInvokeNotPluggable
     * @param $input
     * @param $expected
     */
    public function testInvokeNotPluggable($input, $expected)
    {
        $sut = new SubmissionSectionDetails();

        $mockView = m::mock('\Zend\View\Renderer\RendererInterface');

        $sut->setView($mockView);

        $this->assertEquals(
            $expected,
            $sut(
                $input['submissionSection'],
                $input['data']
            )
        );
    }

    public function provideInvoke()
    {
        return [
            [['submissionSection' => '', 'data' => []], ''],
            [['submissionSection' => [], 'data' => []], ''],
            [['submissionSection' => null, 'data' => []], ''],
            [['submissionSection' => false, 'data' => []], ''],
            [['submissionSection' => 'rubbish', 'data' => []], ''],
            [['submissionSection' => 'submission_section_intr', 'data' => []], ''],

        ];
    }

    public function provideInvokeNotPluggable()
    {
        return [
            [['submissionSection' => 'rubbish', 'data' => []], ''],
            [['submissionSection' => 'submission_section_intr', 'data' => []], '']
        ];
    }
}
