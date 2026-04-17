<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\VariationSafety;
use Laminas\Form\Form;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Variation Safety Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VariationSafetyTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var VariationSafety
     */
    protected $sut;

    protected $fh;

    public function setUp(): void
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sut = new VariationSafety($this->fh);
    }

    public function testAlterForm(): void
    {
        $mockForm = m::mock(Form::class);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\Safety')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->cancel')
            ->once()
            ->getMock();

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
