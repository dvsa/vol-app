<?php

namespace OlcsTest\Controller\Lva\Variation;

use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Mockery as m;
use Common\Service\Entity\LicenceEntityService;

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

    protected function setupGetUndertakingsData($applicationData)
    {
        $mockTransferAnnotationBuilder = m::mock();
        $this->setService('TransferAnnotationBuilder', $mockTransferAnnotationBuilder);

        $mockQueryService = m::mock();
        $this->setService('QueryService', $mockQueryService);

        $mockResponse = m::mock();

        $this->sut->shouldReceive('getIdentifier')->andReturn(12);

        $mockTransferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type('Dvsa\Olcs\Transfer\Query\Application\Declaration'))->once()->andReturn('QUERY');

        $mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResponse);

        $mockResponse->shouldReceive('isOk')->andReturn(true);
        $mockResponse->shouldReceive('getResult')->andReturn($applicationData);
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
            'licence' => ['licenceType' => ['id' => 'ltyp_r']],
            'canHaveInterimLicence' => true,
            'isLicenceUpgrade' => true
        ];

        $this->setupGetUndertakingsData($applicationData);

        $expectedFormData = [
            'declarationsAndUndertakings' => [
                'declarationConfirmation' => 'N',
                'version' => 1,
                'id' => '123',
                'undertakings' => 'markup-undertakings-gv80a',
                'additionalUndertakings' => 'markup-additional-undertakings-gv80a',
            ],
            'interim' => [
                'goodsApplicationInterim' => 'Y',
                'goodsApplicationInterimReason' => 'reason',
            ],
        ];
        $form->shouldReceive('setData')
            ->once()
            ->with($expectedFormData)
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
                    ->with('value', 'REVIEW LINK')
                    ->getMock()
                )
                ->getMock()
            );

        $this->mockRender();

        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        $mockTranslator
            ->shouldReceive('translate')
            ->with('view-full-application')
            ->andReturn('view-full-application')
            ->shouldReceive('translateReplace')
            ->with('undertakings_summary_download', ['URL', 'view-full-application'])
            ->andReturn('REVIEW LINK');

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
            'licence' => ['licenceType' => ['id' => 'ltyp_r']],
            'canHaveInterimLicence' => true,
            'isLicenceUpgrade' => true,
        ];

        $this->setupGetUndertakingsData($applicationData);

        $form->shouldReceive('setData')
            ->once()
            ->andReturnSelf();

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
                            ->with('value', 'REVIEW LINK')
                            ->getMock()
                    )
                    ->getMock()
            );

        $this->mockRender();

        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        $mockTranslator
            ->shouldReceive('translate')
            ->with('view-full-application')
            ->andReturn('view-full-application')
            ->shouldReceive('translateReplace')
            ->with('undertakings_summary_download', ['URL', 'view-full-application'])
            ->andReturn('REVIEW LINK');

        $this->sut->shouldReceive('url->fromRoute')
            ->with('lva-variation/review', [], [], true)
            ->andReturn('URL');

        $this->sut->indexAction();

        $this->assertEquals('undertakings', $this->view);
    }

    public function testPostIndexAction()
    {
        $applicationId = '123';
        $this->sut->shouldReceive('getApplicationId')->andReturn($applicationId);

        $this->setPost();

        $applicationData = [
            'licenceType' => ['id' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL],
            'goodsOrPsv' => ['id' => LicenceEntityService::LICENCE_CATEGORY_PSV],
            'niFlag' => 'N',
            'declarationConfirmation' => 'N',
            'version' => 1,
            'id' => $applicationId,
            'canHaveInterimLicence' => false,
            'isLicenceUpgrade' => false,
        ];

        $this->setupGetUndertakingsData($applicationData);

        $this->mockService('Script', 'loadFile')->once()->with('undertakings');

        $this->mockService('Helper\Translation', 'translate')
            ->with('view-full-application')
            ->andReturn('View full application');

        $this->mockService('Helper\Translation', 'translateReplace')
            ->with('undertakings_summary_download', ['URL', 'View full application'])
            ->andReturn('REVIEW LINK');

        $form = $this->createMockForm('Lva\VariationUndertakings');

        $form->shouldReceive('setData')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getData')
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('get->get->setAttribute')
            ->with('value', 'REVIEW LINK');

        $this->getMockFormHelper()->shouldReceive('remove')
            ->with($form, 'interim');

        $this->sut->shouldReceive('url->fromRoute')
            ->with('lva-variation/review', [], [], true)
            ->andReturn('URL');

        $this->sut->shouldReceive('save');
        $this->sut->shouldReceive('completeSection')->once()->with('undertakings');

        $this->sut->indexAction();
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
