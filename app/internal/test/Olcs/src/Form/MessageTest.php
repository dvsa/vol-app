<?php

declare(strict_types=1);

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

    public function setUp(): void
    {
        $this->sut = new \Olcs\Form\Message();

        $messageElement = new \Common\Form\Elements\Types\HtmlTranslated('message');
        $okButtonElement = new \Laminas\Form\Element('ok');

        $messageFieldSet = new \Laminas\Form\Fieldset('messages');
        $messageFieldSet->add($messageElement);
        $this->sut->add($messageFieldSet);

        $formActionsFieldSet = new \Laminas\Form\Fieldset('form-actions');
        $formActionsFieldSet->add($okButtonElement);
        $this->sut->add($formActionsFieldSet);

        parent::setUp();
    }

    public function testSetMessageString(): void
    {
        $this->sut->setMessage('Foo');

        $this->assertSame('Foo', $this->sut->get('messages')->get('message')->getValue());
    }

    public function testSetMessageArray(): void
    {
        $this->sut->setMessage(['Foo', 'Bar']);

        $this->assertSame('%s<br>%s<br>', $this->sut->get('messages')->get('message')->getValue());
        $this->assertSame(['Foo', 'Bar'], $this->sut->get('messages')->get('message')->getTokens());
    }

    public function testSetMessageArrayKeys(): void
    {
        $this->sut->setMessage(['Foo' => 'Foo description', 'Bar' => 'Bar description']);

        $this->assertSame('%s<br>%s<br>', $this->sut->get('messages')->get('message')->getValue());
        $this->assertSame(['Foo', 'Bar'], $this->sut->get('messages')->get('message')->getTokens());
    }

    public function testSetOkButtonLabel(): void
    {
        $this->sut->setOkButtonLabel('Foo');

        $this->assertSame('Foo', $this->sut->get('form-actions')->get('ok')->getLabel());
    }

    public function testRemoveOkButton(): void
    {
        $this->sut->removeOkButton();

        $this->assertSame(0, $this->sut->get('form-actions')->count());
    }
}
