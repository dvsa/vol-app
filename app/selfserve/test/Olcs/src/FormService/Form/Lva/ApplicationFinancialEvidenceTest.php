<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationFinancialEvidence;
use Zend\Form\Form;
use Zend\Http\Request;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application Financial Evidence Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationFinancialEvidenceTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationFinancialEvidence
     */
    protected $sut;

    protected $fh;

    public function setUp()
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sut = new ApplicationFinancialEvidence();
        $this->sut->setFormHelper($this->fh);
    }

    public function testAlterForm()
    {
        $mockForm = m::mock(Form::class);
        $mockRequest = m::mock(Request::class);

        $this->fh->shouldReceive('createFormWithRequest')
            ->once()
            ->with('Lva\FinancialEvidence', $mockRequest)
            ->andReturn($mockForm)
            ->getMock();

        $this->mockAlterButtons($mockForm, $this->fh);

        $form = $this->sut->getForm($mockRequest);

        $this->assertSame($mockForm, $form);
    }
}

