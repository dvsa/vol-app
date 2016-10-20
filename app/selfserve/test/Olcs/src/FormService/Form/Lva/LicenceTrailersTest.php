<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Data\Object\Bundle\Licence;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\LicenceTrailers;
use Zend\Form\Form;
use Zend\Http\Request;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Licence Trailers Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceTrailersTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var LicenceTrailers
     */
    protected $sut;

    protected $fh;

    public function setUp()
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sut = new LicenceTrailers();
        $this->sut->setFormHelper($this->fh);
    }

    public function testAlterForm()
    {
        $mockRequest = m::mock(Request::class);
        $mockTable = m::mock(TableBuilder::class);

        $mockForm = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('table')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('table')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setTable')
                    ->with($mockTable)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('save')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setAttribute')
                    ->with('class', 'action--primary large')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->getMock();

        $this->fh->shouldReceive('createFormWithRequest')
            ->once()
            ->with('Lva\Trailers', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->saveAndContinue')
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->cancel')
            ->once()
            ->getMock();

        $form = $this->sut->getForm($mockRequest, $mockTable);

        $this->assertSame($mockForm, $form);
    }
}

