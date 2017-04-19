<?php

/**
 * Bus Service Controller Test
 */
namespace OlcsTest\Controller\Bus\Service;

use Olcs\Controller\Bus\Service\BusServiceController as Sut;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Common\RefData;

/**
 * Bus Service Controller Test
 */
class BusServiceControllerTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new Sut;
    }

    public function testGetForm()
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $licenceId = 110;
        $params = m::mock()
            ->shouldReceive('fromRoute')->with('licence')->andReturn($licenceId)
            ->getMock();

        $this->sut
            ->shouldReceive('params')
            ->once()
            ->andReturn($params);

        $type = 'foo';

        $mockTableFieldset = m::mock('\Zend\Form\Fieldset');

        $mockConditionsFieldset = m::mock('\Zend\Form\Fieldset');
        $mockConditionsFieldset->shouldReceive('get')->with('table')->andReturn($mockTableFieldset);

        $mockForm = m::mock('\Zend\Form\Form');
        $mockForm->shouldReceive('get')->with('conditions')->andReturn($mockConditionsFieldset);
        $mockForm->shouldReceive('hasAttribute')->with('action')->andReturnNull();
        $mockForm->shouldReceive('setAttribute')->with('action', '');

        $mockFormHelper = m::mock('Common\Form\View\Helper\Form');
        $mockFormHelper->shouldReceive('createForm')->with($type)->andReturn($mockForm);
        $mockFormHelper->shouldReceive('setFormActionFromRequest')->with(
            $mockForm,
            m::type('object')
        )->andReturn($mockForm);
        $mockFormHelper->shouldReceive('populateFormTable')->with(
            m::type('object'),
            m::type('array')
        )->andReturn($mockForm);

        $mockTableService = m::mock('\Common\Service\Table\TableFactory');
        $mockTableService->shouldReceive('prepareTable')->with(
            m::type('string'),
            m::type('array')
        )->andReturn(['tabledata']);

        $response = m::mock()
            ->shouldReceive('isServerError')
            ->andReturn(false)
            ->shouldReceive('isClientError')
            ->andReturn(false)
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(
                [
                    'results' => [],
                    'count' => 0,
                ]
            )
            ->getMock();

        $this->sut
            ->shouldReceive('handleQuery')
            ->once()
            ->andReturn($response);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTableService);

        $this->sut->setServiceLocator($mockSl);

        $result = $this->sut->getForm($type);

        $this->assertSame($result, $mockForm);
    }

    /**
     * @dataProvider alterFormForEditDataProvider
     *
     * @param $data
     * @param $readonly
     * @param $timetableRemoved
     * @param $opNotifiedLaPteRemoved
     */
    public function testAlterFormForEdit(
        $data,
        $readonly,
        $timetableRemoved,
        $opNotifiedLaPteRemoved,
        $laShortNoteRemoved
    ) {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $response = m::mock();
        $response->shouldReceive('getResult')
            ->once()
            ->andReturn($data);

        $busRegId = 56;
        $params = m::mock()
            ->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId)
            ->getMock();

        $this->sut
            ->shouldReceive('handleQuery')
            ->once()
            ->andReturn($response);

        $this->sut
            ->shouldReceive('params')
            ->once()
            ->andReturn($params);

        $mockFieldset = m::mock('\Zend\Form\Element');
        $mockFieldset->shouldReceive('get')->with('fields')->andReturn($mockFieldset);
        $mockFieldset->shouldReceive('remove')
            ->times($opNotifiedLaPteRemoved ? 1 : 0)
            ->with('opNotifiedLaPte');
        $mockFieldset->shouldReceive('remove')
            ->times($laShortNoteRemoved ? 1 : 0)
            ->with('laShortNote');

        $mockTimetableFieldset = m::mock(\Zend\Form\Fieldset::class);
        $mockTimetableFieldset->shouldReceive('remove')
            ->with('timetableAcceptable')
            ->times($timetableRemoved ? 1 : 0)
            ->andReturnSelf();
        $mockTimetableFieldset->shouldReceive('remove')
            ->with('mapSupplied')
            ->times($timetableRemoved ? 1 : 0)
            ->andReturnSelf();

        $mockForm = m::mock('\Zend\Form\Form');
        $mockForm->shouldReceive('get')->with('fields')->andReturn($mockFieldset);
        $mockForm->shouldReceive('get')
            ->with('timetable')
            ->times($timetableRemoved ? 1 : 0)
            ->andReturn($mockTimetableFieldset);
        $mockForm->shouldReceive('setOption')
            ->times($readonly ? 1 : 0)
            ->with('readonly', true);

        $result = $this->sut->alterFormForEdit($mockForm, []);

        $this->assertSame($mockForm, $result);
    }

    public function alterFormForEditDataProvider()
    {
        return [
            [
                [
                    'isReadOnly' => true,
                    'isScottishRules' => true,
                    'isShortNotice' => 'Y',
                    'isCancelled' => 1,
                    'isCancellation' => 0,
                ],
                // $readonly
                true,
                // $timetableRemoved
                true,
                // $opNotifiedLaPteRemoved
                false,
                // $laShortNoteRemoved
                false
            ],
            [
                [
                    'isReadOnly' => true,
                    'isScottishRules' => true,
                    'isShortNotice' => 'Y',
                    'isCancelled' => 0,
                    'isCancellation' => 1,
                ],
                // $readonly
                true,
                // $timetableRemoved
                true,
                // $opNotifiedLaPteRemoved
                false,
                // $laShortNoteRemoved
                false
            ],
            [
                [
                    'isReadOnly' => true,
                    'isScottishRules' => true,
                    'isShortNotice' => 'Y',
                    'isCancelled' => 0,
                    'isCancellation' => 0,
                ],
                // $readonly
                true,
                // $timetableRemoved
                false,
                // $opNotifiedLaPteRemoved
                false,
                // $laShortNoteRemoved
                false
            ],
            [
                [
                    'isReadOnly' => false,
                    'isScottishRules' => false,
                    'isShortNotice' => 'Y',
                    'isCancelled' => 0,
                    'isCancellation' => 0,
                ],
                // $readonly
                false,
                // $timetableRemoved
                false,
                // $opNotifiedLaPteRemoved
                true,
                // $laShortNoteRemoved
                false
            ],
            [
                [
                    'isReadOnly' => false,
                    'isScottishRules' => true,
                    'isShortNotice' => 'N',
                    'isCancelled' => 0,
                    'isCancellation' => 0,
                ],
                // $readonly
                false,
                // $timetableRemoved
                false,
                // $opNotifiedLaPteRemoved
                false,
                // $laShortNoteRemoved
                true
            ],
            [
                [
                    'isReadOnly' => false,
                    'isScottishRules' => false,
                    'isShortNotice' => 'N',
                    'isCancelled' => 0,
                    'isCancellation' => 0,
                ],
                // $readonly
                false,
                // $timetableRemoved
                false,
                // $opNotifiedLaPteRemoved
                true,
                // $laShortNoteRemoved
                true
            ],
        ];
    }
}
