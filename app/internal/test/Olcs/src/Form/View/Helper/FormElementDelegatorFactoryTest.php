<?php

namespace OlcsTest\Form\View\Helper;

use Laminas\Form\View\Helper\FormElement;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Form\View\Helper\FormElementDelegatorFactory;

/**
 * FormElementDelegatorFactoryTest
 */
class FormElementDelegatorFactoryTest extends MockeryTestCase
{
    public function testInvoke()
    {
        $sm = m::mock(ServiceLocatorInterface::class);

        $requestedName = 'foo';

        $formElement = m::mock(FormElement::class);
        $formElement->shouldReceive('addClass')
            ->with('\Olcs\Form\Element\SubmissionSections', 'formSubmissionSections')
            ->once();

        $callback = function () use ($formElement) {
            return $formElement;
        };

        $sut = new FormElementDelegatorFactory();
        $return = $sut($sm, $requestedName, $callback);

        $this->assertSame($formElement, $return);
    }

    /**
     * @todo OLCS-28149
     */
    public function testCreateDelegatorWithName()
    {
        $sm = m::mock(ServiceLocatorInterface::class);

        $name = 'foo';
        $requestedName = 'foo';

        $formElement = m::mock(FormElement::class);
        $formElement->shouldReceive('addClass')
            ->with('\Olcs\Form\Element\SubmissionSections', 'formSubmissionSections')
            ->once();

        $callback = function () use ($formElement) {
            return $formElement;
        };

        $sut = new FormElementDelegatorFactory();
        $return = $sut->createDelegatorWithName($sm, $name, $requestedName, $callback);

        $this->assertSame($formElement, $return);
    }
}
