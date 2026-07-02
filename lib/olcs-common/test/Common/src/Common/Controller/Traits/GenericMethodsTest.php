<?php

declare(strict_types=1);

namespace CommonTest\Controller\Traits;

use Common\Controller\Traits\GenericMethods;
use Common\Service\Helper\FormHelperService;
use CommonTest\Common\Controller\Traits\Stubs\GenericMethodsStub;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers GenericMethods
 */
class GenericMethodsTest extends MockeryTestCase
{
    /** @var  GenericMethodsStub | m\MockInterface */
    private $sut;

    /** @var  m\MockInterface | FormHelperService */
    private $mockHlpForm;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockHlpForm = m::mock(FormHelperService::class);
        $this->sut = m::mock(GenericMethodsStub::class, [$this->mockHlpForm])->makePartial();
    }

    public function testGetForm(): void
    {
        $class = 'unit_path_to_class';

        $mockReq = m::mock(Request::class);
        $mockForm = m::mock(Form::class);

        $this->sut
            ->shouldReceive('getRequest')->twice()->andReturn($mockReq);

        $this->mockHlpForm
            ->shouldReceive('createForm')->once()->with($class)->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest')->once()->with($mockForm, $mockReq)
            ->shouldReceive('processAddressLookupForm')->once()->with($mockForm, $mockReq);

        static::assertSame($mockForm, $this->sut->getForm($class));
    }

    public function testGenerateFormWithData(): void
    {
        $class = 'unit_path_to_class';
        $callback = static function (): void {
        };
        $data = ['unit_data'];
        $fieldVals = ['unit_fieldValues'];

        $mockReq = m::mock(Request::class);
        $mockReq->shouldReceive('isPost')->once()->andReturn(false);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('setData')->once()->with($data);

        $this->sut
            ->shouldReceive('getRequest')->once()->andReturn($mockReq)
            ->shouldReceive('getForm')->once()->with($class)->andReturn($mockForm)
            ->shouldReceive('formPost')->once()->with($mockForm, $callback, [], true, $fieldVals)->andReturn($mockForm);

        static::assertSame($mockForm, $this->sut->generateFormWithData($class, $callback, $data, false, $fieldVals));
    }
}
