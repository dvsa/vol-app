<?php

namespace OlcsTest\Controller\Lva\Licence;

use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Mockery as m;

/**
 * Test External Licence Operating Centres Controller
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
            ->with('authorisation_in_form', [])
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
}
