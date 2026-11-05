<?php

declare(strict_types=1);

namespace CommonTest\FormService\Form\Lva;

use Common\FeatureToggle;
use Common\Form\Elements\InputFilters\SmsPhone;
use Common\FormService\Form\Lva\SmsNumberRequiredTrait;
use Common\Service\Cqrs\Query\QuerySender;
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

    private function applyTrait(Form $form, bool $captureOn): void
    {
        $querySender = $this->createMock(QuerySender::class);
        $querySender->expects($this->once())
            ->method('featuresEnabled')
            ->with([FeatureToggle::SMS_NUMBER_CAPTURE])
            ->willReturn($captureOn);

        $sut = new class {
            use SmsNumberRequiredTrait;

            public function run(Form $form, QuerySender $querySender): void
            {
                $this->requireSmsNumber($form, $querySender);
            }
        };
        $sut->run($form, $querySender);
    }

    public function testSmsNumberBecomesRequired(): void
    {
        $form = $this->buildForm();
        $input = $form->getInputFilter()->get('contact')->get('phone_sms');
        $this->assertFalse($input->isRequired());

        $this->applyTrait($form, true);

        $this->assertTrue($form->getInputFilter()->get('contact')->get('phone_sms')->isRequired());
        $this->assertSame('contact-number-sms', $form->get('contact')->get('phone_sms')->getLabel());
    }

    public function testEmptySmsNumberFailsWhenRequired(): void
    {
        $form = $this->buildForm();
        $this->applyTrait($form, true);
        $form->setData(['contact' => ['phone_sms' => '']]);

        $this->assertFalse($form->isValid());
        $this->assertNotEmpty($form->getMessages()['contact']['phone_sms']);
    }

    public function testSmsNumberStaysOptionalWhenCaptureOff(): void
    {
        $form = $this->buildForm();
        $form->get('contact')->get('phone_sms')->init();
        $this->applyTrait($form, false);

        $this->assertSame('contact-number-sms-optional', $form->get('contact')->get('phone_sms')->getLabel());

        $this->assertFalse($form->getInputFilter()->get('contact')->get('phone_sms')->isRequired());

        $form->setData(['contact' => ['phone_sms' => '']]);
        $this->assertTrue($form->isValid());
    }
}
