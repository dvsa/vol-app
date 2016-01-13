<?php

namespace OlcsTest\Form;

/**
 * MessageTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class MessageTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var \Olcs\Form\Message
     */
    private $sut;

    public function setUp()
    {
        $this->sut = new \Olcs\Form\Message();

        $messageElement = new \Common\Form\Elements\Types\HtmlTranslated('message');
        $okButtonElement = new \Zend\Form\Element('ok');

        $messageFieldSet = new \Zend\Form\Fieldset('messages');
        $messageFieldSet->add($messageElement);
        $this->sut->add($messageFieldSet);

        $formActionsFieldSet = new \Zend\Form\Fieldset('form-actions');
        $formActionsFieldSet->add($okButtonElement);
        $this->sut->add($formActionsFieldSet);

        parent::setUp();
    }

    public function testSetMessageString()
    {
        $this->sut->setMessage('Foo');

        $this->assertSame('Foo', $this->sut->get('messages')->get('message')->getValue());
    }

    public function testSetMessageArray()
    {
        $this->sut->setMessage(['Foo', 'Bar']);

        $this->assertSame('%s<br>%s<br>', $this->sut->get('messages')->get('message')->getValue());
        $this->assertSame(['Foo', 'Bar'], $this->sut->get('messages')->get('message')->getTokens());
    }

    public function testSetMessageArrayKeys()
    {
        $this->sut->setMessage(['Foo' => 'Foo description', 'Bar' => 'Bar description']);

        $this->assertSame('%s<br>%s<br>', $this->sut->get('messages')->get('message')->getValue());
        $this->assertSame(['Foo', 'Bar'], $this->sut->get('messages')->get('message')->getTokens());
    }

    public function testSetOkButtonLabel()
    {
        $this->sut->setOkButtonLabel('Foo');

        $this->assertSame('Foo', $this->sut->get('form-actions')->get('ok')->getLabel());
    }

    public function testRemoveOkButton()
    {
        $this->sut->removeOkButton();

        $this->assertSame(0, $this->sut->get('form-actions')->count());
    }
}
