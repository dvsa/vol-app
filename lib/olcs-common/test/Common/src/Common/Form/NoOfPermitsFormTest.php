<?php

namespace CommonTest\Form\View\Helper;

use Common\Form\NoOfPermitsForm;
use Common\Form\Elements\Custom\NoOfPermits as NoOfPermitsElement;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Number;
use Laminas\Form\Fieldset;

/**
 * NoOfPermitsFormTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsFormTest extends TestCase
{
    public function testParentNotValidNoZeroesCheckRequired(): void
    {
        $existingErrorMessages = ['Existing error message 1', 'Existing error message 2'];

        $noOfPermitsForm = m::mock(NoOfPermitsForm::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $noOfPermitsForm->shouldReceive('callParentGetMessages')
            ->andReturn($existingErrorMessages);
        $noOfPermitsForm->shouldReceive('callParentIsValid')
            ->andReturn(false);

        $this->assertFalse($noOfPermitsForm->isValid());
        $this->assertEquals($existingErrorMessages, $noOfPermitsForm->getMessages());
    }

    public function testParentValidAllZeroFields(): void
    {
        $noOfPermitsForm = $this->createParentValidForm(false, false, false, false);
        $noOfPermitsForm->shouldReceive('callParentGetMessages')
            ->andReturn([]);

        $this->assertFalse($noOfPermitsForm->isValid());

        $this->assertEquals(
            ['permits.page.no-of-permits.error.at-least-one'],
            $noOfPermitsForm->getMessages()
        );
    }

    public function testParentValidSomeZeroFields(): void
    {
        $noOfPermitsForm = $this->createParentValidForm(false, true, false, true);
        $this->assertTrue($noOfPermitsForm->isValid());
    }

    public function testParentValidNoZeroFields(): void
    {
        $noOfPermitsForm = $this->createParentValidForm(true, true, true, true);
        $this->assertTrue($noOfPermitsForm->isValid());
    }

    private function createParentValidForm(
        bool $element1NonZeroValue,
        bool $element2NonZeroValue,
        bool $element3NonZeroValue,
        bool $element4NonZeroValue
    ): m\LegacyMockInterface {
        $topLevelFieldset1 = new Fieldset('topLevelFieldset1');
        $topLevelFieldset1->add($this->createNoOfPermitsElementMock('noOfPermitsElement1', $element1NonZeroValue));
        $topLevelFieldset1->add($this->createNoOfPermitsElementMock('noOfPermitsElement2', $element2NonZeroValue));
        $topLevelFieldset1->add(new Number('numberElement1'));

        $nestedFieldset2 = new Fieldset('nestedFieldset2');
        $nestedFieldset2->add($this->createNoOfPermitsElementMock('noOfPermitsElement3', $element3NonZeroValue));
        $nestedFieldset2->add(new Text('textElement1'));

        $topLevelFieldset3 = new Fieldset('topLevelFieldset3');
        $topLevelFieldset3->add($this->createNoOfPermitsElementMock('noOfPermitsElement4', $element4NonZeroValue));
        $topLevelFieldset3->add(new Number('numberElement2'));
        $topLevelFieldset3->add($nestedFieldset2);

        $permitsRequiredFieldset = new Fieldset('permitsRequiredFieldset');
        $permitsRequiredFieldset->add($topLevelFieldset1);
        $permitsRequiredFieldset->add($topLevelFieldset3);

        $fieldsFieldset = new Fieldset('fieldsFieldset');
        $fieldsFieldset->add($permitsRequiredFieldset);

        $noOfPermitsForm = m::mock(NoOfPermitsForm::class . '[callParentIsValid, callParentGetMessages]')
            ->shouldAllowMockingProtectedMethods();
        $noOfPermitsForm->add($fieldsFieldset);

        $noOfPermitsForm->shouldReceive('callParentIsValid')
            ->andReturn(true);

        return $noOfPermitsForm;
    }

    private function createNoOfPermitsElementMock(string $name, $hasNonZeroValue): NoOfPermitsElement
    {
        $noOfPermitsElement = m::mock(NoOfPermitsElement::class);
        $noOfPermitsElement->shouldReceive('getName')
            ->andReturn($name);
        $noOfPermitsElement->shouldReceive('hasNonZeroValue')
            ->andReturn($hasNonZeroValue);

        return $noOfPermitsElement;
    }
}
