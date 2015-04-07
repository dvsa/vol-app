<?php

/**
 * Internal Licencing Overview Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Licence;

use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

use Common\Service\Entity\LicenceEntityService as Licence;
use Common\Service\Entity\ApplicationEntityService as Application;

/**
 * Internal Licencing Overview Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Licence\OverviewController');
    }

    public function testCreateVariationAction()
    {
        $licenceId = 3;
        $varId = 5;

        $mockApplicationService = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $mockApplicationService->shouldReceive('createVariation')
            ->with($licenceId)
            ->andReturn($varId);

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-variation', ['application' => $varId])
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->createVariationAction());
    }

    /**
     * @dataProvider indexProvider
     * @param array $overviewData
     * @param array $expectedViewData
     * @param boolean $shouldRemoveTcArea
     * @param boolean $shouldRemoveReviewDate
     */
    public function testIndexActionGet(
        $overviewData,
        $shouldRemoveTcArea,
        $shouldRemoveReviewDate
    ) {
        $licenceId = 123;
        $organisationId = 72;

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $form = $this->createMockForm('LicenceOverview');

        $mockLicenceEntity = $this->mockEntity('Licence', 'getExtendedOverview')
            ->once()
            ->with($licenceId)
            ->andReturn($overviewData);

        $viewData = ['foo' => 'bar'];

        $this->mockService('Helper\LicenceOverview', 'getViewData')
            ->with($overviewData)
            ->once()
            ->andReturn($viewData);

        $this->mockTcAreaSelect($form);

        $form->shouldReceive('setData')
            ->once()
            ->with(
                [
                    'details' => [
                        'continuationDate' => '2017-06-05',
                        'reviewDate'       => '2016-05-04',
                        'leadTcArea'       => 'B',
                    ],
                    'id' => $licenceId,
                    'version' => 1,
                ]
            )
            ->andReturnSelf();

        if ($shouldRemoveReviewDate) {
            $this->getMockFormHelper()
                ->shouldReceive('remove')
                ->once()
                ->with($form, 'details->reviewDate');
        }

        if ($shouldRemoveTcArea) {
            $this->getMockFormHelper()
                ->shouldReceive('remove')
                ->once()
                ->with($form, 'details->leadTcArea');
        }

        $this->mockRender();

        $view = $this->sut->indexAction();

        foreach ($viewData as $key => $value) {
            $this->assertEquals($value, $view->getVariable($key), "'$key' not as expected");
        }
    }

    public function indexProvider()
    {
        return [
            'valid goods licence' => [
                // overviewData
                [
                    'id'           => 123,
                    'version'      => 1,
                    'reviewDate'   => '2016-05-04',
                    'expiryDate'   => '2017-06-05',
                    'status'       => ['id' => Licence::LICENCE_STATUS_VALID],
                    'organisation' => [
                        'id' => 72,
                        'licences' => [
                            ['id' => 210],
                            ['id' => 208],
                            ['id' => 203],
                        ],
                        'leadTcArea' => ['id' => 'B'],
                    ],
                ],
                // shouldRemoveTcArea
                false,
                // shouldRemoveReviewDate,
                false,
            ],
            'surrendered psv licence' => [
                // overviewData
                [
                    'id'           => 123,
                    'version'      => 1,
                    'reviewDate'   => '2016-05-04',
                    'expiryDate'   => '2017-06-05',
                    'status'       => ['id' => Licence::LICENCE_STATUS_SURRENDERED],
                    'organisation' => [
                        'id' => 72,
                        'licences' => [
                            ['id' => 210],
                            ['id' => 208],
                            ['id' => 203],
                        ],
                        'leadTcArea' => ['id' => 'B'],
                    ],
                ],
                // shouldRemoveTcArea
                false,
                // shouldRemoveReviewDate,
                true,
            ],
            'special restricted psv licence' => [
                // overviewData
                [
                    'id'           => 123,
                    'version'      => 1,
                    'reviewDate'   => '2016-05-04',
                    'expiryDate'   => '2017-06-05',
                    'status'       => ['id' => Licence::LICENCE_STATUS_VALID],
                    'organisation' => [
                        'id' => 72,
                        'licences' => [
                            ['id' => 210],
                        ],
                        'leadTcArea' => ['id' => 'B'],
                    ],
                ],
                // shouldRemoveTcArea
                true,
                // shouldRemoveReviewDate,
                false,
            ],
        ];
    }

    public function testIndexActionPostValid()
    {
        $licenceId = 123;
        $organisationId = 234;

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $form = $this->createMockForm('LicenceOverview');

        $overviewData = [
            'id' => $licenceId,
            'status' => ['id' => Licence::LICENCE_STATUS_VALID],
            'organisation' => [
                'id' => $organisationId,
                'licences' => [
                    ['id' => 69],
                    ['id' => 70],
                ],
            ],
        ];
        $mockLicenceEntity = $this->mockEntity('Licence', 'getExtendedOverview')
            ->with($licenceId)
            ->andReturn($overviewData);

        $postData = [
            'id' => $licenceId,
            'version' => '1',
            'details' => [
                'continuationDate' => [
                    'day' => '04',
                    'month' => '03',
                    'year' => '2012'
                ],
                'reviewDate' => [
                    'day' => '11',
                    'month' => '12',
                    'year' => '2021'
                ],
                'leadTcArea' => 'B',
            ],
        ];

        $this->setPost($postData);

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $this->getMockFormHelper()
            ->shouldReceive('remove')
            ->with($form, 'details->reviewDate');

        $this->mockTcAreaSelect($form);

        // use a real date helper
        $dateHelper = new \Common\Service\Helper\DateHelperService();
        $this->setService('Helper\Date', $dateHelper);

        $mockLicenceEntity->shouldReceive('forceUpdate')->with(
            123,
            [
                'expiryDate' => '2012-03-04',
                'reviewDate' => '2021-12-11',
            ]
        );

        $this->mockEntity('Organisation', 'forceUpdate')
            ->with($organisationId, ['leadTcArea' => 'B']);

        $this->sut->shouldReceive('addSuccessMessage')->once();
        $this->sut->shouldReceive('reload')->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionPostInvalid()
    {
        $licenceId = 123;
        $organisationId = 72;

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $form = $this->createMockForm('LicenceOverview');

        $overviewData = [
            'id'           => 123,
            'version'      => 1,
            'reviewDate'   => '2016-05-04',
            'expiryDate'   => '2017-06-05',
            'status'       => ['id' => Licence::LICENCE_STATUS_VALID],
            'organisation' => [
                'id' => 72,
                'licences' => [
                    ['id' => 210],
                    ['id' => 208],
                    ['id' => 203],
                ],
                'leadTcArea' => ['id' => 'B'],
            ],
        ];

        $mockLicenceEntity = $this->mockEntity('Licence', 'getExtendedOverview')
            ->with($licenceId)
            ->andReturn($overviewData);

        $postData = [
            'id' => $licenceId,
            'version' => '1',
            'details' => [],
        ];

        $this->setPost($postData);

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        $this->getMockFormHelper()
            ->shouldReceive('remove')
            ->with($form, 'details->reviewDate');

        $this->mockService('Helper\LicenceOverview', 'getViewData')
            ->with($overviewData)
            ->once()
            ->andReturn([]);

        $this->mockTcAreaSelect($form);

        $mockLicenceEntity->shouldReceive('forceUpdate')->never();

        $this->mockEntity('Organisation', 'forceUpdate')->never();

        $this->mockRender();

        $view = $this->sut->indexAction();
    }

    protected function mockTcAreaSelect($form)
    {
        $tcAreaOptions = [
            'A' => 'Traffic area A',
            'B' => 'Traffic area A',
        ];

        $this->mockEntity('TrafficArea', 'getValueOptions')
            ->andReturn($tcAreaOptions);

        $form->shouldReceive('get')->with('details')->andReturn(
            m::mock()
                ->shouldReceive('get')
                    ->with('leadTcArea')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setValueOptions')
                            ->with($tcAreaOptions)
                            ->getMock()
                    )
                ->getMock()
        );
    }

    public function testPrintAction()
    {
        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn(123);

        $this->mockService('Processing\Licence', 'generateDocument')
            ->with(123);

        $this->sut->shouldReceive('addSuccessMessage')->once();

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('lva-licence/overview', [], [], true)
            ->andReturn('RESPONSE');

        $this->assertEquals(
            'RESPONSE',
            $this->sut->printAction()
        );
    }
}
