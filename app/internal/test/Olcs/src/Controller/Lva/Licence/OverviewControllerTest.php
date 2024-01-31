<?php

/**
 * Internal Licencing Overview Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace OlcsTest\Controller\Lva\Licence;

use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Transfer\Command\Licence\Overview as OverviewCommand;
use Dvsa\Olcs\Transfer\Query\Licence\Overview as OverviewQuery;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Mockery as m;
use Olcs\Controller\Lva\Licence\OverviewController;
use Olcs\Service\Helper\LicenceOverviewHelperService;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Licencing Overview Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->mockNiTextTranslationUtil = m::mock(NiTextTranslation::class);
        $this->mockAuthService = m::mock(AuthorizationService::class);
        $this->mockLicenceOverviewHelper = m::mock(LicenceOverviewHelperService::class);
        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockNavigation = m::mock('navigation');
        $this->mockFlashMessenger = m::mock(FlashMessengerHelperService::class);
        $this->mockController(OverviewController::class, [
           $this->mockNiTextTranslationUtil,
            $this->mockAuthService,
            $this->mockLicenceOverviewHelper,
            $this->mockFormHelper,
            $this->mockNavigation,
            $this->mockFlashMessenger
        ]);
    }

    /**
     * @dataProvider indexProvider
     * @param array $overviewData
     * @param boolean $shouldRemoveTcArea
     * @param boolean $shouldRemoveReviewDate
     */
    public function testIndexActionGet($overviewData, $shouldRemoveReviewDate)
    {
        $licenceId = 123;

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $form = m::mock(Form::class);

        $this->expectQuery(OverviewQuery::class, ['id' => $licenceId], $overviewData);

        $viewData = ['foo' => 'bar'];

        $this->mockLicenceOverviewHelper->shouldReceive('getViewData')
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
                        'translateToWelsh' => 'Y',
                        'leadTcArea'       => 'B',
                    ],
                    'id' => $licenceId,
                    'version' => 1,
                ]
            )
            ->andReturnSelf();

        if ($shouldRemoveReviewDate) {
            $this->mockFormHelper
                ->shouldReceive('remove')
                ->once()
                ->with($form, 'details->reviewDate');
        }

        $this->mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('LicenceOverview')
            ->andReturn($form);

        $this->mockRender();

        $view = $this->sut->indexAction();
        $this->assertEquals('sections/licence/pages/overview', $view->getTemplate());

        foreach ($viewData as $key => $value) {
            $this->assertEquals($value, $view->getVariable($key), "'$key' not as expected");
        }
    }

    public function indexProvider()
    {
        $valueOptions = [
            'trafficAreas' => [
                'A' => 'Traffic area A',
                'B' => 'Traffic area B',
            ],
        ];

        return [
            'valid goods licence' => [
                [
                    'id'           => 123,
                    'version'      => 1,
                    'translateToWelsh' => 'Y',
                    'reviewDate'   => '2016-05-04',
                    'expiryDate'   => '2017-06-05',
                    'status'       => ['id' => RefData::LICENCE_STATUS_VALID],
                    'organisation' => [
                        'id' => 72,
                        'licences' => [
                            ['id' => 210],
                            ['id' => 208],
                            ['id' => 203],
                        ],
                        'leadTcArea' => ['id' => 'B', 'isWales' => true],
                    ],
                    'trafficArea' => ['id' => 'B', 'isWales' => true],
                    'valueOptions' => $valueOptions,
                ],
                false,
            ],
            'surrendered psv licence' => [
                [
                    'id'           => 123,
                    'version'      => 1,
                    'translateToWelsh' => 'Y',
                    'reviewDate'   => '2016-05-04',
                    'expiryDate'   => '2017-06-05',
                    'status'       => ['id' => RefData::LICENCE_STATUS_SURRENDERED],
                    'organisation' => [
                        'id' => 72,
                        'licences' => [
                            ['id' => 210],
                            ['id' => 208],
                            ['id' => 203],
                        ],
                        'leadTcArea' => ['id' => 'B', 'isWales' => true],
                    ],
                    'trafficArea' => ['id' => 'B', 'isWales' => true],
                    'valueOptions' => $valueOptions,
                ],
                true,
            ],
            'special restricted psv licence' => [
                [
                    'id'           => 123,
                    'version'      => 1,
                    'translateToWelsh' => 'Y',
                    'reviewDate'   => '2016-05-04',
                    'expiryDate'   => '2017-06-05',
                    'status'       => ['id' => RefData::LICENCE_STATUS_VALID],
                    'organisation' => [
                        'id' => 72,
                        'licences' => [
                            ['id' => 210],
                        ],
                        'leadTcArea' => ['id' => 'B', 'isWales' => true],
                    ],
                    'trafficArea' => ['id' => 'B', 'isWales' => true],
                    'valueOptions' => $valueOptions,
                ],
                false,
            ],
        ];
    }

    public function testIndexActionPostValidSaveSuccess()
    {
        $licenceId = 123;
        $organisationId = 234;

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $form = m::mock(Form::class);

        $overviewData = [
            'id' => $licenceId,
            'status' => ['id' => RefData::LICENCE_STATUS_VALID],
            'organisation' => [
                'id' => $organisationId,
                'leadTcArea' => ['id' => 'B', 'isWales' => false],
                'licences' => [
                    ['id' => 69],
                    ['id' => 70],
                ],
            ],
            'trafficArea' => ['id' => 'B', 'isWales' => false],
            'valueOptions' => [
                'trafficAreas' => [
                    'A' => 'Traffic area A',
                    'B' => 'Traffic area B',
                ],
            ],
        ];

        $this->expectQuery(OverviewQuery::class, ['id' => $licenceId], $overviewData);

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
                'translateToWelsh' => 'N',
            ],
        ];

        $formData = [
            'id' => $licenceId,
            'version' => '1',
            'details' => [
                'continuationDate' => '2012-03-04',
                'reviewDate' =>  '2021-12-11',
                'leadTcArea' => 'B',
                'translateToWelsh' => 'N',
            ],
        ];

        $expectedCmdData = [
            'id' => $licenceId,
            'version' => '1',
            'leadTcArea' => 'B',
            'expiryDate' => '2012-03-04',
            'reviewDate' => '2021-12-11',
            'translateToWelsh' => 'N',
        ];

        $this->setPost($postData);

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf()
            ->shouldReceive('getData')
            ->andReturn($formData);

        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $this->mockFormHelper
            ->shouldReceive('remove')
            ->with($form, 'details->reviewDate')
            ->shouldReceive('remove')
            ->with($form, 'details->translateToWelsh');

        $this->mockTcAreaSelect($form);

        $this->mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('LicenceOverview')
            ->andReturn($form);

        $this->expectCommand(
            OverviewCommand::class,
            $expectedCmdData,
            [
                'id' => [
                    'licence' => $licenceId,
                ],
                'messages' => [
                    'licence updated',
                ]
            ]
        );

        $this->sut->shouldReceive('addSuccessMessage')->once();
        $this->sut->shouldReceive('reload')->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionPostValidSaveFails()
    {
        $licenceId = 123;
        $organisationId = 234;

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $form = m::mock(Form::class);

        $overviewData = [
            'id' => $licenceId,
            'status' => ['id' => RefData::LICENCE_STATUS_VALID],
            'organisation' => [
                'id' => $organisationId,
                'leadTcArea' => ['id' => 'B', 'isWales' => false],
                'licences' => [
                    ['id' => 69],
                    ['id' => 70],
                ],
            ],
            'trafficArea' => ['id' => 'B', 'isWales' => false],
            'valueOptions' => [
                'trafficAreas' => [
                    'A' => 'Traffic area A',
                    'B' => 'Traffic area B',
                ],
            ],
        ];

        $this->expectQuery(OverviewQuery::class, ['id' => $licenceId], $overviewData);

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

        $formData = [
            'id' => $licenceId,
            'version' => '1',
            'details' => [
                'continuationDate' => '2012-03-04',
                'reviewDate' =>  '2021-12-11',
                'leadTcArea' => 'B',
            ],
        ];

        $expectedCmdData = [
            'id' => $licenceId,
            'version' => '1',
            'leadTcArea' => 'B',
            'expiryDate' => '2012-03-04',
            'reviewDate' => '2021-12-11',
            'translateToWelsh' => null,
        ];

        $this->setPost($postData);

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf()
            ->shouldReceive('getData')
            ->andReturn($formData);

        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $this->mockFormHelper
            ->shouldReceive('remove')
            ->with($form, 'details->reviewDate')
            ->shouldReceive('remove')
            ->with($form, 'details->translateToWelsh');

        $this->mockTcAreaSelect($form);

        $this->expectCommand(
            OverviewCommand::class,
            $expectedCmdData,
            [
                'messages'  => [
                    'failed',
                ]
            ],
            false
        );

        $this->sut->shouldReceive('addErrorMessage')->once();

        $this->mockLicenceOverviewHelper->shouldReceive('getViewData')
            ->with($overviewData)
            ->once()
            ->andReturn([]);

        $this->mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('LicenceOverview')
            ->andReturn($form);

        $this->mockTcAreaSelect($form);

        $this->mockRender();

        $view = $this->sut->indexAction();
        $this->assertEquals('sections/licence/pages/overview', $view->getTemplate());
    }

    public function testIndexActionPostInvalid()
    {
        $licenceId = 123;

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $form = m::mock(Form::class);

        $overviewData = [
            'id'           => 123,
            'version'      => 1,
            'reviewDate'   => '2016-05-04',
            'expiryDate'   => '2017-06-05',
            'status'       => ['id' => RefData::LICENCE_STATUS_VALID],
            'organisation' => [
                'id' => 72,
                'licences' => [
                    ['id' => 210],
                    ['id' => 208],
                    ['id' => 203],
                ],
                'leadTcArea' => ['id' => 'B', 'isWales' => true],
            ],
            'trafficArea' => ['id' => 'B', 'isWales' => true],
            'valueOptions' => [
                'trafficAreas' => [
                    'A' => 'Traffic area A',
                    'B' => 'Traffic area B',
                ],
            ],
        ];

        $this->expectQuery(OverviewQuery::class, ['id' => $licenceId], $overviewData);

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

        $this->mockLicenceOverviewHelper->shouldReceive('getViewData')
            ->with($overviewData)
            ->once()
            ->andReturn([]);

        $this->mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('LicenceOverview')
            ->andReturn($form);

        $this->mockTcAreaSelect($form);

        $this->mockRender();

        $view = $this->sut->indexAction();
        $this->assertEquals('sections/licence/pages/overview', $view->getTemplate());
    }

    protected function mockTcAreaSelect($form)
    {
        $tcAreaOptions = [
            'A' => 'Traffic area A',
            'B' => 'Traffic area B',
        ];

        $form->shouldReceive('get')->with('details')->andReturn(
            m::mock(ElementInterface::class)
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

    public function testIndexActionUnlicensedRedirect()
    {
        $licenceId = 123;
        $organisationId = 1;

        $overviewData = [
            'id' => $licenceId,
            'status' => [
                'id' => RefData::LICENCE_STATUS_UNLICENSED,
            ],
            'organisation' => [
                'id' => $organisationId,
                'isUnlicensed' => true,
            ],
        ];

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $this->mockFormHelper->shouldReceive('createForm')
            ->with('LicenceOverview');

        $this->expectQuery(OverviewQuery::class, ['id' => $licenceId], $overviewData);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('operator-unlicensed', ['organisation' => 1])
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }
}
