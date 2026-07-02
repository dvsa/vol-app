<?php

namespace CommonTest\Form\View\Helper;

use Common\Form\QaForm;
use Common\Service\Qa\IsValidHandlerInterface;
use Common\Service\Qa\DataHandlerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;

/**
 * QaFormTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class QaFormTest extends MockeryTestCase
{
    public function testSetData(): void
    {
        $unprocessedData = [
            'unprocessedKey1' => 'unprocessedValue1',
            'unprocessedKey2' => 'unprocessedValue2'
        ];

        $processedData = [
            'processedKey1' => 'unprocessedValue1',
            'processedKey2' => 'unprocessedValue2'
        ];

        $qaForm = m::mock(QaForm::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $qaForm->shouldReceive('updateDataForQa')
            ->with($unprocessedData)
            ->once()
            ->andReturn($processedData);
        $qaForm->shouldReceive('callParentSetData')
            ->with($processedData)
            ->once();

        $qaForm->setData($unprocessedData);
    }

    public function testSetDataForRedisplayWithHandler(): void
    {
        $formControlType = 'form_control_type';

        $applicationStep = [
            'type' => $formControlType
        ];

        $data = [
            'prop1' => 'value1',
            'prop2' => 'value2'
        ];

        $qaForm = m::mock(QaForm::class)->makePartial();
        $qaForm->shouldReceive('setData')
            ->with($data)
            ->once()
            ->globally()
            ->ordered();

        $dataHandler = m::mock(DataHandlerInterface::class);
        $dataHandler->shouldReceive('setData')
            ->with($qaForm)
            ->once()
            ->globally()
            ->ordered();

        $qaForm->registerDataHandler($formControlType, $dataHandler);
        $qaForm->setApplicationStep($applicationStep);

        $qaForm->setDataForRedisplay($data);
    }

    public function testSetDataForRedisplayWithoutHandler(): void
    {
        $formControlType = 'form_control_type';

        $applicationStep = [
            'type' => $formControlType
        ];

        $data = [
            'prop1' => 'value1',
            'prop2' => 'value2'
        ];

        $qaForm = m::mock(QaForm::class)->makePartial();
        $qaForm->shouldReceive('setData')
            ->with($data)
            ->once()
            ->globally()
            ->ordered();

        $qaForm->setApplicationStep($applicationStep);
        $qaForm->setDataForRedisplay($data);
    }

    public function testIsValidParentReturnsFalse(): void
    {
        $qaForm = m::mock(QaForm::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $qaForm->shouldReceive('callParentIsValid')
            ->andReturn(false);

        $this->assertFalse($qaForm->isValid());
    }

    /**
     * @dataProvider dpIsValidParentReturnsTrueWithHandler
     */
    public function testIsValidParentReturnsTrueWithHandler($isValidHandlerResponse): void
    {
        $formControlType = 'form_control_type';

        $applicationStep = [
            'type' => $formControlType
        ];

        $qaForm = m::mock(QaForm::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $qaForm->shouldReceive('callParentIsValid')
            ->andReturn(true);

        $isValidHandler = m::mock(IsValidHandlerInterface::class);
        $isValidHandler->shouldReceive('isValid')
            ->with($qaForm)
            ->once()
            ->andReturn($isValidHandlerResponse);

        $qaForm->setApplicationStep($applicationStep);
        $qaForm->registerIsValidHandler($formControlType, $isValidHandler);

        $this->assertEquals(
            $isValidHandlerResponse,
            $qaForm->isValid()
        );
    }

    /**
     * @return bool[][]
     *
     * @psalm-return list{list{true}, list{false}}
     */
    public function dpIsValidParentReturnsTrueWithHandler(): array
    {
        return [
            [true],
            [false]
        ];
    }

    public function testIsValidParentReturnsTrueWithoutHandler(): void
    {
        $formControlType = 'form_control_type';

        $applicationStep = [
            'type' => $formControlType
        ];

        $qaForm = m::mock(QaForm::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $qaForm->shouldReceive('callParentIsValid')
            ->andReturn(true);

        $qaForm->setApplicationStep($applicationStep);

        $this->assertTrue($qaForm->isValid());
    }

    public function testIsValidParentReturnsTrueValidationPreventedWithoutHandler(): void
    {
        $formControlType = 'form_control_type';

        $applicationStep = [
            'type' => $formControlType
        ];

        $qaForm = m::mock(QaForm::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $qaForm->preventSuccessfulValidation();
        $qaForm->shouldReceive('callParentIsValid')
            ->andReturn(true);

        $qaForm->setApplicationStep($applicationStep);

        $this->assertFalse($qaForm->isValid());
    }

    public function testSetGetApplicationStep(): void
    {
        $applicationStep = [
            'prop1' => 'value1',
            'prop2' => 'value2',
        ];

        $qaForm = new QaForm();
        $qaForm->setApplicationStep($applicationStep);

        $this->assertEquals(
            $applicationStep,
            $qaForm->getApplicationStep()
        );
    }

    public function testGetQuestionFieldsetData(): void
    {
        $fieldset87Data = [
            'qaElement' => 'qaElementValue',
            'fieldset87Prop2' => 'fieldset87Value2'
        ];

        $data = [
            'qa' => [
                'myname' => [
                    'mynameProp1' => 'mynameValue1',
                    'mynameProp2' => 'mynameValue2',
                ],
                'fieldset87' => $fieldset87Data,
                'Submit' => [
                    'fieldset87Prop1' => 'fieldset87Value1',
                    'fieldset87Prop2' => 'fieldset87Value2'
                ],
            ]
        ];

        $qaFieldset = new Fieldset('qa');

        $questionFieldset = new Fieldset('fieldset87');

        $qaFieldset->add(new Fieldset('myname'));
        $qaFieldset->add($questionFieldset);
        $qaFieldset->add(new Fieldset('Submit'));

        $qaForm = new QaForm();
        $qaForm->add($qaFieldset);
        $qaForm->setData($data);

        $this->assertEquals(
            $fieldset87Data,
            $qaForm->getQuestionFieldsetData()
        );
    }

    public function testGetQuestionFieldset(): void
    {
        $qaForm = new QaForm();

        $qaFieldset = new Fieldset('qa');

        $questionFieldset = new Fieldset('fieldset87');

        $qaFieldset->add(new Fieldset('myname'));
        $qaFieldset->add($questionFieldset);
        $qaFieldset->add(new Fieldset('Submit'));

        $qaForm->add($qaFieldset);

        $this->assertSame(
            $questionFieldset,
            $qaForm->getQuestionFieldset()
        );
    }
}
