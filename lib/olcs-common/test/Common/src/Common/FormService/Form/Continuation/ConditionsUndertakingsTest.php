<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Continuation\ConditionsUndertakings;
use Common\Form\Model\Form\Continuation\ConditionsUndertakings as ConditionsUndertakingsForm;
use Common\Service\Helper\FormHelperService;

/**
 * Licence checklist form service test
 */
class ConditionsUndertakingsTest extends MockeryTestCase
{
    /** @var ConditionsUndertakingsForm */
    protected $sut;

    /** @var  m\MockInterface */
    private $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->sut = new ConditionsUndertakings($this->formHelper);
    }

    public function testGetForm(): void
    {
        $form = m::mock(ConditionsUndertakingsForm::class);

        $this->formHelper
            ->shouldReceive('createForm')
            ->with(ConditionsUndertakingsForm::class)
            ->andReturn($form)
            ->once()
            ->getMock();

        $this->assertEquals($form, $this->sut->getForm());
    }
}
