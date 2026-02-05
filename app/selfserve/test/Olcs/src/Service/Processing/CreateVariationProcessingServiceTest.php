<?php

declare(strict_types=1);

namespace OlcsTest\Service\Processing;

use Common\Form\Form;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Transfer\Command\CommandContainerInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVariation;
use Laminas\Form\Element;
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

    public function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->annotationBuilder = m::mock(AnnotationBuilder::class);
        $this->commandService = m::mock(CommandService::class);

        $this->sut = new CreateVariationProcessingService(
            $this->formHelper,
            $this->annotationBuilder,
            $this->commandService
        );
    }

    public function testGetDataFromForm(): void
    {
        $form = m::mock(\Laminas\Form\Form::class);

        $this->assertEquals([], $this->sut->getDataFromForm($form));
    }

    public function testCreateVariation(): void
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

    public function testGetForm(): void
    {
        // Params
        $mockRequest = m::mock(\Laminas\Http\Request::class);

        // Mocks
        $mockForm = m::mock(Form::class);

        // Expectations
        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('GenericConfirmation', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        $mockForm->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock(Element::class)
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

    public function testGetFormWithPost(): void
    {
        // Params
        $mockRequest = m::mock(\Laminas\Http\Request::class);
        $postData = ['foo' => 'bar'];

        // Mocks
        $mockForm = m::mock(Form::class);

        // Expectations
        $this->formHelper->shouldReceive('createFormWithRequest')
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
                m::mock(Element::class)
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
