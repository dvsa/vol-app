<?php

/**
 * NysiisFactory Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
namespace Dvsa\OlcsTest\Api\Service;

use Olcs\Service\Data\NysiisFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * NysiisFactory Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class NysiisFactoryTest extends MockeryTestCase
{
    public function setUp()
    {
        $this->sm = m::mock('\Zend\ServiceManager\ServiceLocatorInterface');

        parent::setUp();
    }

    private function createService()
    {
        $config = [
            'nysiis' => [
                'wsdl' => [
                    'uri' => 'wsdlFile'
                ]
            ]
        ];
        $this->sm->shouldReceive('get')
            ->with('Config')
            ->andReturn($config);

        $sut = new NysiisFactory();
        return $sut->createService($this->sm);
    }

    public function testCreateService()
    {
        $this->createService();
    }

}
