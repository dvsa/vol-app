<?php

/**
 * External Type Of Licence Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Application;

use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * External Type Of Licence Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;
    protected $adapter;

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\Lva\Application\TypeOfLicenceController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);

        $this->adapter = m::mock('\Common\Controller\Lva\Interfaces\TypeOfLicenceAdapterInterface');
        $this->sut->setTypeOfLicenceAdapter($this->adapter);
    }

    /**
     * @group application_type_of_licence
     */
    public function testConfirmationActionWithRedirect()
    {
        $response = m::mock('\Zend\Http\Response');

        $this->adapter->shouldReceive('confirmationAction')
            ->andReturn($response);

        $this->assertSame($response, $this->sut->confirmationAction());
    }

    /**
     * @group application_type_of_licence
     */
    public function testConfirmationAction()
    {
        $response = m::mock('\Zend\Form\Form');

        $this->adapter->shouldReceive('confirmationAction')
            ->andReturn($response);

        $this->sut->shouldReceive('render')
            ->with(
                'type_of_licence_confirmation',
                $response,
                ['sectionText' => 'application_type_of_licence_confirmation_subtitle']
            )
            ->andReturn('RESPONSE');

        $this->assertSame('RESPONSE', $this->sut->confirmationAction());
    }
}
