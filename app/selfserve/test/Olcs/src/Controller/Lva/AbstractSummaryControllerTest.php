<?php

namespace OlcsTest\Controller\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\TransportManagerApplicationEntityService as TmaService;

/**
 * AbstractSummaryControllerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class AbstractSummaryControllerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\Mock
     */
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock(\Olcs\Controller\Lva\AbstractSummaryController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testIndexAction()
    {
        $applicationData = [
            'id' => 712,
            'licence' => ['licNo' => 'LIC_NO'],
            'status' => ['id' => 'STATUS', 'description' => 'DESCRIPTION'],
            'receivedDate' => 'RECEIVED_DATE',
            'targetCompletionDate' => 'TARGET_COMPLETION_DATE',
            'actions' => 'ACTIONS',
            'transportManagers' => 'TRANSPORT_MANAGERS',
            'outstandingFee' => 'OUTSTANDING_FEE',
            'isVariation' => true,
            'licenceType' => ['id' => \Common\RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            'goodsOrPsv' => ['id' => \Common\RefData::LICENCE_CATEGORY_GOODS_VEHICLE],
            'appliedVia' => ['id' => \Common\RefData::APPLIED_VIA_SELFSERVE],
            'interimStart' => '2016-01-01',
            'interimStatus' => [
                'description' => 'In-Force'
            ],
        ];
        $this->sut
            ->shouldReceive('getIdentifier')->with()->once()->andReturn(712)
            ->shouldReceive('handleQuery')->once()->andReturn(
                m::mock()->shouldReceive('getResult')->andReturn($applicationData)->getMock()
            )
            ->shouldReceive('render')->once()->andReturnUsing(
                function ($view) {
                    $this->assertSame(
                        [
                            'justPaid' => true,
                            'lva' => null,
                            'licence' => 'LIC_NO',
                            'application' => 712,
                            'canWithdraw' => false,
                            'status' => 'DESCRIPTION',
                            'submittedDate' => 'RECEIVED_DATE',
                            'completionDate' => 'TARGET_COMPLETION_DATE',
                            'paymentRef' => 'REF',
                            'actions' => 'ACTIONS',
                            'transportManagers' => 'TRANSPORT_MANAGERS',
                            'outstandingFee' => 'OUTSTANDING_FEE',
                            'importantText' => 'application-summary-important-goods-var',
                            'hideContent' => false,
                            'interimStatus' => 'In-Force',
                            'interimStart' => '2016-01-01',
                        ],
                        $view->getVariables()
                    );
                    return 'RENDERED';
                }
            )
            ->shouldReceive('params->fromRoute')->with('reference')->twice()->andReturn('REF');

        $this->assertSame('RENDERED', $this->sut->indexAction());
    }


    /**
     * @dataProvider dataProviderImportantText
     */
    public function testImportantText($isVariation, $goodsOrPsv, $licenceType, $expected)
    {
        $applicationData = [
            'id' => 712,
            'licence' => ['licNo' => 'LIC_NO'],
            'status' => ['id' => 'STATUS', 'description' => 'DESCRIPTION'],
            'receivedDate' => 'RECEIVED_DATE',
            'targetCompletionDate' => 'TARGET_COMPLETION_DATE',
            'actions' => 'ACTIONS',
            'transportManagers' => 'TRANSPORT_MANAGERS',
            'outstandingFee' => 'OUTSTANDING_FEE',
            'isVariation' => $isVariation,
            'goodsOrPsv' => ['id' => $goodsOrPsv],
            'licenceType' => ['id' => $licenceType],
            'appliedVia' => ['id' => \Common\RefData::APPLIED_VIA_SELFSERVE]
        ];
        $this->sut
            ->shouldReceive('getIdentifier')->with()->once()->andReturn(712)
            ->shouldReceive('handleQuery')->once()->andReturn(
                m::mock()->shouldReceive('getResult')->andReturn($applicationData)->getMock()
            )
            ->shouldReceive('render')->once()->andReturnUsing(
                function ($view) use ($expected) {
                    $this->assertSame($expected, $view->getVariable('importantText'));
                    return 'RENDERED';
                }
            )
            ->shouldReceive('params->fromRoute')->with('reference')->twice()->andReturn('REF');

        $this->assertSame('RENDERED', $this->sut->indexAction());
    }

    public function dataProviderImportantText()
    {
        return [
            // isVariation, goodsOrPsv, licence type, expected
            [true, \Common\RefData::LICENCE_CATEGORY_GOODS_VEHICLE, 'XX', 'application-summary-important-goods-var'],
            [false, \Common\RefData::LICENCE_CATEGORY_GOODS_VEHICLE, 'XX', 'application-summary-important-goods-app'],
            [true, \Common\RefData::LICENCE_CATEGORY_PSV, 'XX', 'application-summary-important-psv-var'],
            [false, \Common\RefData::LICENCE_CATEGORY_PSV, 'XX', 'application-summary-important-psv-app'],
            [
                false,
                \Common\RefData::LICENCE_CATEGORY_PSV,
                \Common\RefData::LICENCE_TYPE_SPECIAL_RESTRICTED,
                'application-summary-important-psv-app-sr'
            ],
        ];
    }
}
