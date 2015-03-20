<?php

/**
 * Business Details Change Task Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\BusinessService\Service\Lva;

use PHPUnit_Framework_TestCase;
use Olcs\BusinessService\Service\Lva\BusinessDetailsChangeTask;
use Common\BusinessService\Response;

/**
 * Business Details Change Task Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetailsChangeTaskTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $sut = new BusinessDetailsChangeTask();
        $response = $sut->process([]);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_NO_OP, $response->getType());
    }
}
