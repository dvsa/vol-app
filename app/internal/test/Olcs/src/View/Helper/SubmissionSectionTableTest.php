<?php


namespace OlcsTest\View\Helper;

use Olcs\View\Helper\SubmissionSectionTable;
use Olcs\View\Helper\SubmissionSectionTableFactory;
use Mockery as m;

/**
 * Class SubmissionSectionDetails
 * @package OlcsTest\View\Helper
 */
class SubmissionSectionTableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideInvoke
     * @param $input
     * @param $expected
     */
    public function testInvoke($input, $expected)
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
            ->andReturn('<table></table>');

        $sut->setTableBuilder($mockTableBuilder);

        $result = $sut(
            $input['submissionSection'],
            $input['data']
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
                ['submissionSection' => 'introduction', 'data' => ['data' => []]], null
            ],
            [
                ['submissionSection' => '', 'data' => ['data' => []]], ''
            ],
        ];
    }
}
