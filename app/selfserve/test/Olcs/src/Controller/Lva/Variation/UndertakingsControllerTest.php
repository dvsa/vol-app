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

    public function undertakingsPartialProvider() {
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
     * Test edge case logic for determining which undertakings html is shown
     *
     * @dataProvider partialInvalidProvider
     */
    public function testGetUndertakingsPartialInvalid($goodsOrPsv, $typeOfLicence, $niFlag, $isUpgrade, $expectedMessage)
    {
        $this->setExpectedException('\LogicException', $expectedMessage);
        $this->sut->getUndertakingsPartial($goodsOrPsv, $typeOfLicence, $niFlag, $isUpgrade);
    }

    public function partialInvalidProvider() {
        return [
            'invalid goods licence type'      => ['lcat_gv', 'foo', 'N', 0, 'Licence Type not set or invalid'],
            'invalid goods licence type (SR)' => ['lcat_gv', 'ltyp_sr', 'N', 0, 'Licence Type not set or invalid'],
            'invalid psv licence type'        => ['lcat_psv', 'foo', 'N', 0, 'Licence Type not set or invalid'],
            'invalid licence category'        => ['foo', 'ltyp_sr', 'N', 0, 'Licence Category not set or invalid'],
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

    public function declarationsPartialProvider() {
        return [
            'GB Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'N', 0, 'markup-declarations-gv81-standard'],
            'GB Goods Standard International' => ['lcat_gv', 'ltyp_si', 'N', 0, 'markup-declarations-gv81-standard'],
            'GB Goods Restricted no upgrade'  => ['lcat_gv', 'ltyp_r', 'N', 0, 'markup-declarations-gv81-restricted'],
            'GB Goods Restricted upgrade'     => ['lcat_gv', 'ltyp_r', 'N', 1, 'markup-declarations-gv80a'],
            'NI Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'Y', 0, 'markup-declarations-gvni81-standard'],
            'NI Goods Standard International' => ['lcat_gv', 'ltyp_si', 'Y', 0, 'markup-declarations-gvni81-standard'],
            'NI Goods Restricted no upgrade'  => ['lcat_gv', 'ltyp_r', 'Y', 0, 'markup-declarations-gvni81-restricted'],
            'NI Goods Restricted upgrade'     => ['lcat_gv', 'ltyp_r', 'Y', 1, 'markup-declarations-gvni80a'],
            'PSV Standard National'           => ['lcat_psv', 'ltyp_sn', 'N', 0, 'markup-declarations-psv430-431-standard'],
            'PSV Standard International'      => ['lcat_psv', 'ltyp_si', 'N', 0, 'markup-declarations-psv430-431-standard'],
            'PSV Restricted'                  => ['lcat_psv', 'ltyp_r', 'N', 0, 'markup-declarations-psv430-431-restricted'],
            'PSV Special Restricted'          => ['lcat_psv', 'ltyp_sr', 'N', 0, 'markup-declarations-psv430-431-restricted'],
        ];
    }

    /**
     * Test edge case logic for determining which declarations html is shown
     *
     * @dataProvider partialInvalidProvider
     */
    public function testGetDeclarationsPartialInvalid($goodsOrPsv, $typeOfLicence, $niFlag, $isUpgrade, $expectedMessage)
    {
        $this->setExpectedException('\LogicException', $expectedMessage);
        $this->sut->getDeclarationsPartial($goodsOrPsv, $typeOfLicence, $niFlag, $isUpgrade);
    }

    /**
     * @dataProvider undertakingsPartialProvider
     * @dataProvider declarationsPartialProvider
     */
    public function testUndertakingsPartialExists($g, $t, $n, $u, $partial) {

        $this->markTestSkipped('use in dev only!');

        $path = __DIR__ . '/../../../../../../vendor/olcs/OlcsCommon/Common/config/language/partials/';
        $gbFile = $path.'en_GB/'.$partial.'.phtml';
        $cyFile = $path.'cy_GB/'.$partial.'.phtml';
        $this->assertTrue(file_exists($gbFile), "$gbFile not found");
        $this->assertTrue(file_exists($cyFile), "$cyFile not found");
    }
}
