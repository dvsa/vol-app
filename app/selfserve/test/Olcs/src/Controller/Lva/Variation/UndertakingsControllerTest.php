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

    public function testGetIndexAction()
    {
        $form = $this->createMockForm('Lva\VariationUndertakings');

        $applicationId = '123';

        $this->sut->shouldReceive('getApplicationId')->andReturn($applicationId);

        $applicationData = [
            'licenceType' => ['id' => 'ltyp_sn'],
            'goodsOrPsv' => ['id' => 'lcat_gv'],
            'niFlag' => 'N',
            'declarationConfirmation' => 'N',
            'version' => 1,
            'id' => $applicationId,
            'isVariation' => true,
            'licence' => ['licenceType' => ['id' => 'ltyp_r'],]
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

        $expectedFormData = [
            'declarationsAndUndertakings' => [
                'declarationConfirmation' => 'N',
                'version' => 1,
                'id' => $applicationId,
                'undertakings' => 'markup-undertakings-gv80a',
                'additionalUndertakings' => 'markup-additional-undertakings-gv80a',
            ]
        ];

        $form->shouldReceive('setData')
            ->once()
            ->with($expectedFormData)
            ->andReturnSelf();

        $form->shouldReceive('get')
            ->once()
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
                    ->getMock()
            );

        $this->mockRender();

        $this->sut->indexAction();

        $this->assertEquals('undertakings', $this->view);
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
