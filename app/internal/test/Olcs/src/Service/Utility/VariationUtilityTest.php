<?php

/**
 * Variation Utility test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Service\Utility;

use Olcs\Service\Utility\VariationUtility;
use Common\Service\Entity\ApplicationEntityService;

/**
 * Variation Utility Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationUtilityTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new VariationUtility();
    }

    public function testAlterCreateVariationData()
    {
        $data = [
            'foo' => 'bar',
            'status' => 'foo'
        ];

        $expectedData = [
            'foo' => 'bar',
            'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION
        ];

        $this->assertEquals($expectedData, $this->sut->alterCreateVariationData($data));
    }
}
