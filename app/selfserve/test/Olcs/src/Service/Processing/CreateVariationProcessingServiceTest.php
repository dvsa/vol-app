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

        $this->assertEquals([], $this->sut->getDataFromForm($form));
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
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockForm = m::mock();

        // Expectations
        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->with('GenericConfirmation', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        $mockForm->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('submit')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setLabel')
                    ->with('save.continue.button')
                    ->getMock()
                )->getMock()
            );

        $this->assertSame($mockForm, $this->sut->getForm($mockRequest));
    }

    public function testGetFormWithPost()
    {
        // Params
        $mockRequest = m::mock('\Zend\Http\Request');
        $postData = ['foo' => 'bar'];

        // Mocks
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockForm = m::mock();

        // Expectations
        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->with('GenericConfirmation', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('submit')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setLabel')
                    ->with('save.continue.button')
                    ->getMock()
                )->getMock()
            );

        $this->assertSame($mockForm, $this->sut->getForm($mockRequest));
    }
}
