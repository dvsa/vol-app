<?php

namespace OlcsTest\Controller\Lva\Variation;

use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Mockery as m;

/**
 * Test Undertakings (Declarations) Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UndertakingsControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Variation\UndertakingsController');
    }

    protected function getServiceManager()
    {
        return m::mock('\Zend\ServiceManager\ServiceManager')
            ->makePartial()
            ->setAllowOverride(true);
    }

    public function testGetAsGoodsIndexAction()
    {
        $this->mockService('Script', 'loadFile')
            ->with('undertakings');

        $form = $this->createMockForm('Lva\VariationUndertakings');

        $applicationId = '123';

        $this->sut->shouldReceive('getApplicationId')->andReturn($applicationId);

        $applicationData = [
            'licenceType' => ['id' => 'ltyp_sn'],
            'goodsOrPsv' => ['id' => 'lcat_gv'],
            'niFlag' => 'N',
            'declarationConfirmation' => 'N',
            'interimReason' => 'reason',
            'version' => 1,
            'id' => $applicationId,
            'isVariation' => true,
            'licence' => ['licenceType' => ['id' => 'ltyp_r']]
        ];

        $this->sm->shouldReceive('get')->with('Entity\Application')
            ->andReturn(
                m::mock()
                ->shouldReceive('getDataForUndertakings')
                ->once()
                ->with($applicationId)
                ->andReturn($applicationData)
                ->getMock()
            )
            ->shouldReceive('get')->with('Processing\VariationSection')
            ->andReturn(
                m::mock()
                ->shouldReceive('isLicenceUpgrade')
                ->with($applicationId)
                ->andReturn(true)
                ->getMock()
            );

        $form->shouldReceive('setData')
            ->once()
            ->andReturnSelf();

        $this->mockService('Helper\Interim', 'canVariationInterim')
            ->andReturn('false');

        $form->shouldReceive('get')
            ->with('declarationsAndUndertakings')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->once()
                ->with('declarationConfirmation')
                ->andReturn(
                    m::mock()
                        ->shouldReceive('setLabel')
                        ->once()
                        ->with('variation.review-declarations.confirm-text-upgrade')
                        ->getMock()
                )
                ->shouldReceive('get')
                ->with('summaryDownload')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setAttribute')
                    ->with('value', '<p><a href="URL" target="_blank">view-full-application</a></p>')
                    ->getMock()
                )
                ->getMock()
            );

        $this->mockRender();

        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        $mockTranslator->shouldReceive('translate')
            ->with('view-full-application')
            ->andReturn('view-full-application');

        $this->sut->shouldReceive('url->fromRoute')
            ->with('lva-variation/review', [], [], true)
            ->andReturn('URL');

        $this->sut->indexAction();

        $this->assertEquals('undertakings', $this->view);
    }

    public function testGetIndexAsPsv()
    {

        $this->mockService('Script', 'loadFile')
            ->with('undertakings');

        $form = $this->createMockForm('Lva\VariationUndertakings');

        $applicationId = '123';

        $this->sut->shouldReceive('getApplicationId')->andReturn($applicationId);

        $applicationData = [
            'licenceType' => ['id' => 'ltyp_sn'],
            'goodsOrPsv' => ['id' => 'lcat_psv'],
            'niFlag' => 'N',
            'declarationConfirmation' => 'N',
            'interimReason' => 'reason',
            'version' => 1,
            'id' => $applicationId,
            'isVariation' => true,
            'licence' => ['licenceType' => ['id' => 'ltyp_r']]
        ];

        $this->sm->shouldReceive('get')->with('Entity\Application')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getDataForUndertakings')
                    ->once()
                    ->with($applicationId)
                    ->andReturn($applicationData)
                    ->getMock()
            )
            ->shouldReceive('get')->with('Processing\VariationSection')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isLicenceUpgrade')
                    ->with($applicationId)
                    ->andReturn(true)
                    ->getMock()
            );

        $form->shouldReceive('setData')
            ->once()
            ->andReturnSelf();

        $this->sut->shouldReceive('isInterimRequired')
            ->with(
                array(
                    'goodsOrPsv' => array(
                        'lcat_gv'
                    )
                )
            )
            ->andReturn(false);

        $this->getMockFormHelper()->shouldReceive('remove');

        $this->mockService('Helper\Interim', 'canVariationInterim')
            ->andReturn('false');

        $form->shouldReceive('get')
            ->with('declarationsAndUndertakings')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->once()
                    ->with('declarationConfirmation')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setLabel')
                            ->once()
                            ->with('variation.review-declarations.confirm-text-upgrade')
                            ->getMock()
                    )
                    ->shouldReceive('get')
                    ->with('summaryDownload')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setAttribute')
                            ->with('value', '<p><a href="URL" target="_blank">view-full-application</a></p>')
                            ->getMock()
                    )
                    ->getMock()
            );

        $this->mockRender();

        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        $mockTranslator->shouldReceive('translate')
            ->with('view-full-application')
            ->andReturn('view-full-application');

        $this->sut->shouldReceive('url->fromRoute')
            ->with('lva-variation/review', [], [], true)
            ->andReturn('URL');

        $this->sut->indexAction();

        $this->assertEquals('undertakings', $this->view);
    }

    public function testPostIndexAction()
    {
        $this->setPost();

        $this->mockService('Script', 'loadFile')
            ->with('undertakings');

        $form = $this->createMockForm('Lva\VariationUndertakings');

        $this->sut->shouldReceive('getApplicationId');

        $form->shouldReceive('setData')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('isValid')
            ->andReturn(true);

        $this->sut->shouldReceive('formatDataForSave');
        $this->sut->shouldReceive('postSave')->with('undertakings');
        $this->sut->shouldReceive('handleFees');

        $this->mockService('Entity\Application', 'getDataForUndertakings')
            ->with(1)
            ->shouldReceive('save');

        $this->sut->shouldReceive('completeSection')
            ->with('undertakings');

        $this->sut->indexAction();
    }

    public function formatDataForSaveProvider()
    {
        return array(
            array(
                array(
                    'interim' => array(
                        'goodsApplicationInterim' => 'Y',
                        'goodsApplicationInterimReason' => 'reason'
                    ),
                    'declarationsAndUndertakings' => array(
                    )
                ),
                array(
                    'interimStatus' => 'int_sts_requested',
                    'interimReason' => 'reason'
                )
            ),
            array(
                array(
                    'interim' => array(
                        'goodsApplicationInterim' => 'N',
                    ),
                    'declarationsAndUndertakings' => array(
                    )
                ),
                array(
                    'interimStatus' => null,
                    'interimReason' => null
                )
            ),
            array(
                array(
                    'interim' => array(
                        'goodsApplicationInterim' => null,
                    ),
                    'declarationsAndUndertakings' => array(
                    )
                ),
                array(
                    'interimStatus' => null,
                    'interimReason' => null
                )
            ),
        );
    }

    /**
     * @dataProvider formatDataForSaveProvider
     */
    public function testFormatDataForSave($data, $expectedResult)
    {
        $this->assertEquals($this->sut->formatDataForSave($data), $expectedResult);
    }

    /**
     * Test the logic for determining which undertakings html is shown
     *
     * @dataProvider undertakingsPartialProvider
     */
    public function testGetUndertakingsPartial($goodsOrPsv, $typeOfLicence, $niFlag, $isUpgrade, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->sut->getUndertakingsPartial($goodsOrPsv, $typeOfLicence, $niFlag, $isUpgrade)
        );
    }

    public function testIsInterimRequiredPsvType()
    {
        $psv = array(
            'id' => 123,
            'goodsOrPsv' => array(
                'id' => 'lcat_psv'
            )
        );

        $result = $this->sut->isInterimRequired($psv);

        $this->assertFalse($result);
    }

    public function undertakingsPartialProvider()
    {
        return [
            'GB Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'N', 0, 'markup-undertakings-gv81-standard'],
            'GB Goods Standard International' => ['lcat_gv', 'ltyp_si', 'N', 0, 'markup-undertakings-gv81-standard'],
            'GB Goods Restricted no upgrade'  => ['lcat_gv', 'ltyp_r', 'N', 0, 'markup-undertakings-gv81-restricted'],
            'GB Goods Restricted upgrade'     => ['lcat_gv', 'ltyp_r', 'N', 1, 'markup-undertakings-gv80a'],
            'NI Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'Y', 0, 'markup-undertakings-gvni81-standard'],
            'NI Goods Standard International' => ['lcat_gv', 'ltyp_si', 'Y', 0, 'markup-undertakings-gvni81-standard'],
            'NI Goods Restricted no upgrade'  => ['lcat_gv', 'ltyp_r', 'Y', 0, 'markup-undertakings-gvni81-restricted'],
            'NI Goods Restricted upgrade'     => ['lcat_gv', 'ltyp_r', 'Y', 1, 'markup-undertakings-gvni80a'],
            'PSV Standard National'           => ['lcat_psv', 'ltyp_sn', 'N', 0,
                'markup-undertakings-psv430-431-standard'],
            'PSV Standard International'      => ['lcat_psv', 'ltyp_si', 'N', 0,
                'markup-undertakings-psv430-431-standard'],
            'PSV Restricted'                  => ['lcat_psv', 'ltyp_r', 'N', 0,
                'markup-undertakings-psv430-431-restricted'],
            'PSV Special Restricted'          => ['lcat_psv', 'ltyp_sr', 'N', 0,
                'markup-undertakings-psv430-431-restricted'],
        ];
    }

    /**
     * Test the logic for determining which 'additional undertakings' html is shown
     *
     * @dataProvider additionalUndertakingsPartialProvider
     */
    public function testGetAdditionalUndertakingsPartial($goodsOrPsv, $typeOfLicence, $niFlag, $isUpgrade, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->sut->getAdditionalUndertakingsPartial($goodsOrPsv, $typeOfLicence, $niFlag, $isUpgrade)
        );
    }

    public function additionalUndertakingsPartialProvider()
    {
        return [
            'GB Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'N', 0, ''],
            'GB Goods Standard International' => ['lcat_gv', 'ltyp_si', 'N', 0, ''],
            'GB Goods Restricted no upgrade'  => ['lcat_gv', 'ltyp_r', 'N', 0, ''],
            'GB Goods Restricted upgrade'     => ['lcat_gv', 'ltyp_r', 'N', 1,
                'markup-additional-undertakings-gv80a'],
            'NI Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'Y', 0, ''],
            'NI Goods Standard International' => ['lcat_gv', 'ltyp_si', 'Y', 0, ''],
            'NI Goods Restricted no upgrade'  => ['lcat_gv', 'ltyp_r', 'Y', 0, ''],
            'NI Goods Restricted upgrade'     => ['lcat_gv', 'ltyp_r', 'Y', 1,
                'markup-additional-undertakings-gvni80a'],
            'PSV Standard National'           => ['lcat_psv', 'ltyp_sn', 'N', 0, ''],
            'PSV Standard International'      => ['lcat_psv', 'ltyp_si', 'N', 0, ''],
            'PSV Restricted'                  => ['lcat_psv', 'ltyp_r', 'N', 0, ''],
            'PSV Special Restricted'          => ['lcat_psv', 'ltyp_sr', 'N', 0, ''],
        ];
    }

    /**
     * Use in DEV only to check all required partials exist
     *
     * @dataProvider undertakingsPartialProvider
     */
    /*
    public function testUndertakingsPartialExists($g, $t, $n, $u, $partial)
    {
        $path = __DIR__ . '/../../../../../../vendor/olcs/OlcsCommon/Common/config/language/partials/';
        $gbFile = $path.'en_GB/'.$partial.'.phtml';
        $cyFile = $path.'cy_GB/'.$partial.'.phtml';
        $this->assertTrue(file_exists($gbFile), "$gbFile not found");
        $this->assertTrue(file_exists($cyFile), "$cyFile not found");
    }
    */
}
