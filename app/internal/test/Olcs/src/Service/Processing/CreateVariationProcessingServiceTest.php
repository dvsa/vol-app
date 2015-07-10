<?php

/**
 * Create Variation Processing Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Service\Processing;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVariation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Olcs\Service\Processing\CreateVariationProcessingService;

/**
 * Create Variation Processing Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateVariationProcessingServiceTest extends MockeryTestCase
{
    protected $sm;
    protected $sut;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new CreateVariationProcessingService();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testGetDataFromForm()
    {
        $form = m::mock('\Zend\Form\Form');

        $form->shouldReceive('getData')
            ->andReturn(['data' => ['foo' => 'bar']]);

        $this->assertEquals(['foo' => 'bar'], $this->sut->getDataFromForm($form));
    }

    public function testCreateVariation()
    {
        $licenceId = 123;
        $data = ['licenceType' => 'bar'];

        $mockTab = m::mock();
        $mockCs = m::mock();

        $result = ['id' => ['application' => 111]];

        $response = m::mock();
        $response->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($result);

        $this->sm->setService('TransferAnnotationBuilder', $mockTab);
        $this->sm->setService('CommandService', $mockCs);

        $mockTab->shouldReceive('createCommand')
            ->with(m::type(CreateVariation::class))
            ->andReturnUsing(
                function (CommandInterface $command) {

                    $data = $command->getArrayCopy();

                    $this->assertEquals(123, $data['id']);
                    $this->assertEquals('bar', $data['licenceType']);

                    return 'COMMAND';
                }
            );

        $mockCs->shouldReceive('send')
            ->with('COMMAND')
            ->andReturn($response);

        $this->assertEquals(111, $this->sut->createVariation($licenceId, $data));
    }

    public function testGetForm()
    {
        // Params
        $mockRequest = m::mock('\Zend\Http\Request');

        // Mocks
        $mockForm = m::mock();
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockDateHelper = m::mock();
        $this->sm->setService('Helper\Date', $mockDateHelper);

        // Expectations
        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->with('CreateVariation', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        $mockDateHelper->shouldReceive('getDate')
            ->andReturn('2014-01-02');

        $mockForm->shouldReceive('setData')
            ->with(['data' => ['receivedDate' => '2014-01-02']]);

        $this->assertSame($mockForm, $this->sut->getForm($mockRequest));
    }

    public function testGetFormWithPost()
    {
        // Params
        $mockRequest = m::mock('\Zend\Http\Request');
        $postData = ['foo' => 'bar'];

        // Mocks
        $mockForm = m::mock();
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Expectations
        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->with('CreateVariation', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData);

        $this->assertSame($mockForm, $this->sut->getForm($mockRequest));
    }
}
