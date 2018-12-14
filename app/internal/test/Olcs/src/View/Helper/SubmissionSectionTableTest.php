<?php


namespace OlcsTest\View\Helper;

use Olcs\View\Helper\SubmissionSectionTable;
use Olcs\View\Helper\SubmissionSectionTableFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class SubmissionSectionDetails
 * @package OlcsTest\View\Helper
 */
class SubmissionSectionTableTest extends TestCase
{
    /**
     * @dataProvider provideInvoke
     * @param $input
     * @param $expected
     * @param $disabled
     */
    public function testInvoke($input, $expected, $disabled)
    {
        $sut = new SubmissionSectionTable();

        $mockView = m::mock('\Zend\View\Renderer\PhpRenderer');

        $mockViewHelper = m::mock('Olcs\View\Helper\SubmissionSectionTable');

        $mockViewHelper->shouldReceive('__invoke');
        $mockView->shouldReceive('plugin')->andReturn($mockViewHelper);
        $mockView->shouldReceive('render');

        $sut->setView($mockView);

        $mockTableBuilder = m::mock('\Common\Service\Table\TableFactory');
        $mockTableBuilder->shouldReceive('buildTable')
            ->withAnyArgs()
            ->andReturnSelf();
        $mockTableBuilder->shouldReceive('setDisabled')
            ->times($disabled ? 1 : 0)
            ->with(true);
        $mockTableBuilder->shouldReceive('render')
            ->withAnyArgs()
            ->andReturn('<table></table>');

        $sut->setTableBuilder($mockTableBuilder);

        $result = $sut(
            $input['submissionSection'],
            $input['data'],
            $disabled
        );

        $this->assertEquals(
            $expected,
            $result
        );
    }

    public function testCreateService()
    {
        $mockTableBuilder = m::mock('\Common\Service\Table\TableFactory');

        $mockSm = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');

        $mockSm->shouldReceive('getServiceLocator')
            ->andReturn($mockSl);

        $mockSl->shouldReceive('get')
            ->with('Table')
            ->andReturn($mockTableBuilder);

        $sut = new SubmissionSectionTableFactory();
        $service = $sut->createService($mockSm);

        $this->assertInstanceOf('Olcs\View\Helper\SubmissionSectionTable', $service);
        $this->assertSame($mockTableBuilder, $service->getTableBuilder());
    }

    public function provideInvoke()
    {
        return [
            [
                ['submissionSection' => 'introduction', 'data' => ['data' => []]], '<table></table>', true
            ],
            [
                ['submissionSection' => '', 'data' => ['data' => []]], '', false
            ],
        ];
    }
}
