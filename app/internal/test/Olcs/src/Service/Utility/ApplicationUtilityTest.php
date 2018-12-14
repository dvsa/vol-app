<?php

/**
 * Application Utility test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Service\Utility;

use Olcs\Service\Utility\ApplicationUtility;
use Common\Service\Entity\ApplicationEntityService;

/**
 * Application Utility Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationUtilityTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new ApplicationUtility();
    }

    public function testAlterCreateApplicationData()
    {
        $data = [
            'foo' => 'bar',
            'status' => 'foo'
        ];

        $expectedData = [
            'foo' => 'bar',
            'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION
        ];

        $this->assertEquals($expectedData, $this->sut->alterCreateApplicationData($data));
    }
}
