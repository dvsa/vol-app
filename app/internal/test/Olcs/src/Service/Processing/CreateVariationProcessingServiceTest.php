<?php

namespace OlcsTest\Service\Processing;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Transfer\Command\CommandContainerInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVariation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Processing\CreateVariationProcessingService;

/**
 * Create Variation Processing Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateVariationProcessingServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var  m\MockInterface */
    protected $formHelper;

    /** @var  m\MockInterface */
    protected $annotationBuilder;

    /** @var  m\MockInterface */
    protected $commandService;

    /** @var  m\MockInterface */
    protected $dateHelper;

    public function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->annotationBuilder = m::mock(AnnotationBuilder::class);
        $this->commandService = m::mock(CommandService::class);
        $this->dateHelper = m::mock(DateHelperService::class);

        $this->sut = new CreateVariationProcessingService(
            $this->formHelper,
            $this->annotationBuilder,
            $this->commandService,
            $this->dateHelper
        );
    }

    public function testGetDataFromForm()
    {
        $form = m::mock(\Laminas\Form\Form::class);

        $form->shouldReceive('getData')
            ->andReturn(['data' => ['foo' => 'bar']]);

        $this->assertEquals(['foo' => 'bar'], $this->sut->getDataFromForm($form));
    }

    public function testCreateVariation()
    {
        $licenceId = 123;
        $data = ['licenceType' => 'bar'];

        $result = ['id' => ['application' => 111]];

        $response = m::mock();
        $response->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($result);

        $commandContainer = m::mock(CommandContainerInterface::class);

        $this->annotationBuilder->shouldReceive('createCommand')
            ->with(m::type(CreateVariation::class))
            ->once()
            ->andReturnUsing(
                function (CommandInterface $command) use ($commandContainer) {
                    $data = $command->getArrayCopy();

                    $this->assertEquals(123, $data['id']);
                    $this->assertEquals('bar', $data['licenceType']);

                    return $commandContainer;
                }
            );

        $this->commandService->shouldReceive('send')
            ->with($commandContainer)
            ->once()
            ->andReturn($response);

        $this->assertEquals(111, $this->sut->createVariation($licenceId, $data));
    }

    public function testGetForm()
    {
        // Params
        $mockRequest = m::mock(\Laminas\Http\Request::class);

        // Mocks
        $mockForm = m::mock();

        // Expectations
        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('CreateVariation', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        $this->dateHelper->shouldReceive('getDate')
            ->andReturn('2014-01-02');

        $mockForm->shouldReceive('setData')
            ->with(['data' => ['receivedDate' => '2014-01-02']])
            ->once();

        $this->assertSame($mockForm, $this->sut->getForm($mockRequest));
    }

    public function testGetFormWithPost()
    {
        // Params
        $mockRequest = m::mock(\Laminas\Http\Request::class);
        $postData = ['foo' => 'bar'];

        // Mocks
        $mockForm = m::mock();

        // Expectations
        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('CreateVariation', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->once();

        $this->assertSame($mockForm, $this->sut->getForm($mockRequest));
    }
}
