<?php

declare(strict_types=1);

namespace CommonTest\FormService\Form\Lva;

use Common\Form\Elements\InputFilters\SmsPhone;
use Common\FormService\Form\Lva\SmsNumberRequiredTrait;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use PHPUnit\Framework\TestCase;

final class SmsNumberRequiredTraitTest extends TestCase
{
    private function buildForm(): Form
    {
        $contact = new Fieldset('contact');
        $contact->add(new SmsPhone('phone_sms'));

        $form = new Form('test');
        $form->add($contact);

        return $form;
    }

    private function require(Form $form): void
    {
        $sut = new class {
            use SmsNumberRequiredTrait;

            public function run(Form $form): void
            {
                $this->requireSmsNumber($form);
            }
        };
        $sut->run($form);
    }

    public function testSmsNumberBecomesRequired(): void
    {
        $form = $this->buildForm();
        $input = $form->getInputFilter()->get('contact')->get('phone_sms');
        $this->assertFalse($input->isRequired());

        $this->require($form);

        $this->assertTrue($form->getInputFilter()->get('contact')->get('phone_sms')->isRequired());
    }

    public function testEmptySmsNumberFailsWhenRequired(): void
    {
        $form = $this->buildForm();
        $this->require($form);
        $form->setData(['contact' => ['phone_sms' => '']]);

        $this->assertFalse($form->isValid());
        $this->assertNotEmpty($form->getMessages()['contact']['phone_sms']);
    }
}
