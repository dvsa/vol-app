<?php

namespace OlcsTest\Controller\Lva\Application;

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

        $this->mockController('\Olcs\Controller\Lva\Application\UndertakingsController');

    }

    protected function getServiceManager()
    {
        return m::mock('\Zend\ServiceManager\ServiceManager')
            ->makePartial()
            ->setAllowOverride(true);
    }

    public function testGetIndexAction()
    {
        $form = $this->createMockForm('Lva\ApplicationUndertakings');

        $applicationId = '123';

        $this->sut->shouldReceive('getApplicationId')->andReturn($applicationId);

        $applicationData = [
            'licenceType' => ['id' => 'ltyp_sn'],
            'goodsOrPsv' => ['id' => 'lcat_gv'],
            'niFlag' => 'N',
            'declarationConfirmation' => 'N',
            'version' => 1,
            'id' => $applicationId,
        ];
        $this->sm->shouldReceive('get')->with('Entity\Application')
            ->andReturn(
                m::mock()
                ->shouldReceive('getDataForUndertakings')
                    ->once()
                    ->with($applicationId)
                    ->andReturn($applicationData)
                ->getMock()
            );

        $expectedFormData = [
            'declarationsAndUndertakings' => [
                'declarationConfirmation' => 'N',
                'version' => 1,
                'id' => $applicationId,
                'undertakings' => 'markup-undertakings-gv79-standard',
            ]
        ];

        $form->shouldReceive('setData')->once()->with($expectedFormData);

        $this->mockRender();

        $this->sut->indexAction();

        $this->assertEquals('undertakings', $this->view);
    }

    /**
     * Special case where we change the declarations label for custom markup
     */
    public function testGetIndexActionPsvSr()
    {
        $form = $this->createMockForm('Lva\ApplicationUndertakings');

        $applicationId = '123';

        $this->sut->shouldReceive('getApplicationId')->andReturn($applicationId);

        $applicationData = [
            'licenceType' => ['id' => 'ltyp_sr'],
            'goodsOrPsv' => ['id' => 'lcat_psv'],
            'niFlag' => 'N',
            'declarationConfirmation' => 'N',
            'version' => 1,
            'id' => $applicationId,
        ];
        $this->sm->shouldReceive('get')->with('Entity\Application')
            ->andReturn(
                m::mock()
                ->shouldReceive('getDataForUndertakings')
                    ->once()
                    ->with($applicationId)
                    ->andReturn($applicationData)
                ->getMock()
            );

        $expectedFormData = [
            'declarationsAndUndertakings' => [
                'declarationConfirmation' => 'N',
                'version' => 1,
                'id' => $applicationId,
                'undertakings' => 'markup-undertakings-psv356',
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
                        ->with('markup-declarations-psv356')
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
    public function testGetUndertakingsPartial($goodsOrPsv, $typeOfLicence, $niFlag, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->sut->getUndertakingsPartial($goodsOrPsv, $typeOfLicence, $niFlag)
        );
    }

    public function undertakingsPartialProvider()
    {
        return [
            'GB Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'N', 'markup-undertakings-gv79-standard'],
            'GB Goods Standard International' => ['lcat_gv', 'ltyp_si', 'N', 'markup-undertakings-gv79-standard'],
            'GB Goods Restricted'             => ['lcat_gv', 'ltyp_r', 'N', 'markup-undertakings-gv79-restricted'],
            'NI Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'Y', 'markup-undertakings-gvni79-standard'],
            'NI Goods Standard International' => ['lcat_gv', 'ltyp_si', 'Y', 'markup-undertakings-gvni79-standard'],
            'NI Goods Restricted'             => ['lcat_gv', 'ltyp_r', 'Y', 'markup-undertakings-gvni79-restricted'],
            'PSV Standard National'           => ['lcat_psv', 'ltyp_sn', 'N', 'markup-undertakings-psv421-standard'],
            'PSV Standard International'      => ['lcat_psv', 'ltyp_si', 'N', 'markup-undertakings-psv421-standard'],
            'PSV Restricted'                  => ['lcat_psv', 'ltyp_r', 'N', 'markup-undertakings-psv421-restricted'],
            'PSV Special Restricted'          => ['lcat_psv', 'ltyp_sr', 'N', 'markup-undertakings-psv356'],
        ];
    }

    /**
     * Use in DEV only to check all required partials exist
     *
     * @dataProvider undertakingsPartialProvider
     */
    /*
    public function testUndertakingsPartialExists($g, $t, $n, $partial)
    {
        $path = __DIR__ . '/../../../../../../vendor/olcs/OlcsCommon/Common/config/language/partials/';
        $gbFile = $path.'en_GB/'.$partial.'.phtml';
        $cyFile = $path.'cy_GB/'.$partial.'.phtml';
        $this->assertTrue(file_exists($gbFile), "$gbFile not found");
        $this->assertTrue(file_exists($cyFile), "$cyFile not found");
    }
    */
}
