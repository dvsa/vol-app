<?php

/**
 * Company Subsidiary Change Task Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\BusinessService\Service\Lva;

use PHPUnit_Framework_TestCase;
use Olcs\BusinessService\Service\Lva\CompanySubsidiaryChangeTask;
use Common\BusinessService\Response;

/**
 * Company Subsidiary Change Task Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CompanySubsidiaryChangeTaskTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $sut = new CompanySubsidiaryChangeTask();
        $response = $sut->process([]);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_NO_OP, $response->getType());
    }
}
