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
        $form = $this->createMockForm('Lva\Undertakings');

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
                ->once()
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
                'declarations' => 'markup-declarations-gv80a',
            ]
        ];

        $form->shouldReceive('setData')->once();//->with($expectedFormData);

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
            'GB Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'N', 0, 'markup-undertakings-gv81'],
            'GB Goods Standard International' => ['lcat_gv', 'ltyp_si', 'N', 0, 'markup-undertakings-gv81'],
            'GB Goods Restricted no upgrade'  => ['lcat_gv', 'ltyp_r', 'N', 0, 'markup-undertakings-gv81'],
            'GB Goods Restricted upgrade'     => ['lcat_gv', 'ltyp_r', 'N', 1, 'markup-undertakings-gv80a'],
            'NI Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'Y', 0, 'markup-undertakings-gvni81'],
            'NI Goods Standard International' => ['lcat_gv', 'ltyp_si', 'Y', 0, 'markup-undertakings-gvni81'],
            'NI Goods Restricted no upgrade'  => ['lcat_gv', 'ltyp_r', 'Y', 0, 'markup-undertakings-gvni81'],
            'NI Goods Restricted upgrade'     => ['lcat_gv', 'ltyp_r', 'Y', 1, 'markup-undertakings-gvni80a'],
            'PSV Standard National'           => ['lcat_psv', 'ltyp_sn', 'N', 0, 'markup-undertakings-psv430-431'],
            'PSV Standard International'      => ['lcat_psv', 'ltyp_si', 'N', 0, 'markup-undertakings-psv430-431'],
            'PSV Restricted'                  => ['lcat_psv', 'ltyp_r', 'N', 0, 'markup-undertakings-psv430-431'],
            'PSV Special Restricted'          => ['lcat_psv', 'ltyp_sr', 'N', 0, 'markup-undertakings-psv430-431'],
        ];
    }

    /**
     * Test the logic for determining which declarations html is shown
     *
     * @dataProvider declarationsPartialProvider
     */
    public function testGetDeclarationsPartial($goodsOrPsv, $typeOfLicence, $niFlag, $isUpgrade, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->sut->getDeclarationsPartial($goodsOrPsv, $typeOfLicence, $niFlag, $isUpgrade)
        );
    }

    public function declarationsPartialProvider()
    {
        return [
            'GB Goods SN'           => ['lcat_gv', 'ltyp_sn', 'N', 0,'markup-declarations-gv81-standard'],
            'GB Goods SI'           => ['lcat_gv', 'ltyp_si', 'N', 0, 'markup-declarations-gv81-standard'],
            'GB Goods R no upgrade' => ['lcat_gv', 'ltyp_r', 'N', 0, 'markup-declarations-gv81-restricted'],
            'GB Goods R upgrade'    => ['lcat_gv', 'ltyp_r', 'N', 1, 'markup-declarations-gv80a'],
            'NI Goods SN'           => ['lcat_gv', 'ltyp_sn', 'Y', 0, 'markup-declarations-gvni81-standard'],
            'NI Goods SI'           => ['lcat_gv', 'ltyp_si', 'Y', 0, 'markup-declarations-gvni81-standard'],
            'NI Goods R no upgrade' => ['lcat_gv', 'ltyp_r', 'Y', 0, 'markup-declarations-gvni81-restricted'],
            'NI Goods R upgrade'    => ['lcat_gv', 'ltyp_r', 'Y', 1, 'markup-declarations-gvni80a'],
            'PSV SN'                => ['lcat_psv', 'ltyp_sn', 'N', 0, 'markup-declarations-psv430-431-standard'],
            'PSV SI'                => ['lcat_psv', 'ltyp_si', 'N', 0, 'markup-declarations-psv430-431-standard'],
            'PSV R'                 => ['lcat_psv', 'ltyp_r', 'N', 0, 'markup-declarations-psv430-431-restricted'],
            'PSV SR'                => ['lcat_psv', 'ltyp_sr', 'N', 0, 'markup-declarations-psv430-431-restricted'],
        ];
    }

    /**
     * @dataProvider undertakingsPartialProvider
     * @dataProvider declarationsPartialProvider
     */
    public function testUndertakingsPartialExists($g, $t, $n, $u, $partial)
    {
        // $this->markTestSkipped('use in DEV only to check all required partials exist');

        // $path = __DIR__ . '/../../../../../../vendor/olcs/OlcsCommon/Common/config/language/partials/';
        // $gbFile = $path.'en_GB/'.$partial.'.phtml';
        // $cyFile = $path.'cy_GB/'.$partial.'.phtml';
        // $this->assertTrue(file_exists($gbFile), "$gbFile not found");
        // $this->assertTrue(file_exists($cyFile), "$cyFile not found");
    }
}
