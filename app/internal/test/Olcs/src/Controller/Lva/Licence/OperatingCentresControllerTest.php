<?php

namespace OlcsTest\Controller\Lva\Licence;

use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Mockery as m;

/**
 * Test Internal Licence Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OperatingCentresControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Licence\OperatingCentresController');
    }

    public function testGetIndexAction()
    {
        $form = $this->createMockForm('Lva\OperatingCentres');

        $form->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('remove')
                ->with('saveAndContinue')
                ->getMock()
            )
            ->shouldReceive('getInputFilter')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('has')
                    ->andReturn(false)
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('setData')
            ->with(
                [
                    'data' => [
                        'noOfOperatingCentres' => 0,
                        'minVehicleAuth' => 0,
                        'maxVehicleAuth' => 0,
                        'minTrailerAuth' => 0,
                        'maxTrailerAuth' => 0,
                        'licenceType' => [
                            'id' => 'ltyp_sn'
                        ]
                    ]
                ]
            )
            ->andReturn($form);

        $this->sut->shouldReceive('getTypeOfLicenceData')
            ->andReturn(
                [
                    'licenceType' => [
                        'id' => 'ltyp_sn'
                    ],
                    'niFlag' => 'N'
                ]
            )
            ->shouldReceive('addVariationInfoMessage')
            ->shouldReceive('getIdentifier')
            ->andReturn(123);

        $this->mockEntity('Licence', 'getOperatingCentresData')
            ->with(123)
            ->andReturn([]);

        $this->mockEntity('LicenceOperatingCentre', 'getAddressSummaryData')
            ->with(123)
            ->andReturn(
                [
                    'Results' => []
                ]
            );

        $this->mockEntity('Licence', 'getTotalAuths')
            ->with(123)
            ->andReturn([]);

        $table = m::mock()
            ->shouldReceive('getColumn')
            ->shouldReceive('setColumn')
            ->getMock();

        $this->mockService('Table', 'prepareTable')
            ->with('lva-operating-centres', [])
            ->andReturn($table);

        $tableElement = m::mock()
            ->shouldReceive('setTable')
            ->with($table)
            ->getMock();

        $tableFieldset = m::mock()
            ->shouldReceive('get')
            ->andReturn($tableElement)
            ->getMock();

        $form->shouldReceive('get')
            ->with('table')
            ->andReturn($tableFieldset);

        $removeFields = [
            'totAuthSmallVehicles',
            'totAuthMediumVehicles',
            'totAuthLargeVehicles',
            'totCommunityLicences'
        ];

        $this->getMockFormHelper()
            ->shouldReceive('removeFieldList')
            ->with($form, 'data', $removeFields)
            ->shouldReceive('remove')
            ->with($form, 'dataTrafficArea');

        $this->mockRender();

        $this->sut->indexAction();
    }

    public function testBasicPostEditAction()
    {
        $form = $this->createMockForm('Lva\OperatingCentre');

        $data = [
            'applicationOperatingCentre' => [
                'id' => '1'
            ],
            'operatingCentre' => [
                'id' => '16'
            ]
        ];

        $dataFilter = m::mock()
            ->shouldReceive('has')
            ->with('noOfVehiclesRequired')
            ->andReturn(false)
            ->shouldReceive('has')
            ->with('noOfTrailersRequired')
            ->andReturn(false)
            ->getMock();

        $addressInputFilter = m::mock()
            ->shouldReceive('get')
            ->with('postcode')
            ->andReturn(
                m::mock()
                ->shouldReceive('setRequired')
                ->with(false)
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValidatorChain')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('attach')
                        ->getMock()
                    )
                    ->getMock()
                )
                ->getMock()
            )
            ->getMock();

        $inputFilter = m::mock()
            ->shouldReceive('get')
            ->with('data')
            ->andReturn($dataFilter)
            ->shouldReceive('get')
            ->with('address')
            ->andReturn($addressInputFilter)
            ->getMock();

        $addressElement = m::mock();

        $form->shouldReceive('setData')
            ->with([])
            ->andReturn($form)
            ->shouldReceive('getInputFilter')
            ->andReturn($inputFilter)
            ->shouldReceive('get')
            ->with('address')
            ->andReturn($addressElement)
            ->shouldReceive('has')
            ->with('advertisements')
            ->andReturn(false)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($data);

        $this->shouldRemoveAddAnother($form);

        $this->getMockFormHelper()
            ->shouldReceive('processAddressLookupForm')
            ->andReturn(false)
            ->shouldReceive('disableElements')
            ->with($addressElement)
            ->shouldReceive('disableValidation')
            ->with($addressInputFilter);

        $this->sut->shouldReceive('getTypeOfLicenceData')
            ->andReturn(
                [
                    'licenceType' => [
                        'id' => 'ltyp_sn'
                    ],
                    'niFlag' => 'N'
                ]
            )
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(4321)
            ->shouldReceive('getLicenceId')
            ->andReturn(7)
            ->shouldReceive('getIdentifier')
            ->andReturn(9)
            ->shouldReceive('handlePostSave')
            ->andReturn('saved');

        $this->mockEntity('Licence', 'getTrafficArea')
            ->with(7)
            ->andReturn(['id' => 'B']);

        $this->mockEntity('LicenceOperatingCentre', 'getOperatingCentresCount')
            ->with(9)
            ->andReturn(
                [
                    'Count' => 0
                ]
            );

        $this->mockEntity('LicenceOperatingCentre', 'getVehicleAuths')
            ->with(4321)
            ->andReturn(
                [
                    'noOfVehicles' => 0,
                    'noOfTrailers' => 0
                ]
            );

        $this->mockEntity('OperatingCentre', 'save')
            ->with(
                [
                    'id' => '16'
                ]
            );

        $this->mockEntity('LicenceOperatingCentre', 'save');

        $removeFields = [
            'advertisements',
            'data->sufficientParking',
            'data->permission'
        ];

        $this->shouldRemoveElements($form, $removeFields);

        $this->setPost();

        $this->assertEquals(
            'saved',
            $this->sut->editAction()
        );
    }
}
