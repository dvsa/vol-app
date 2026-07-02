<?php

namespace CommonTest\Service\Qa\Custom\Common;

use Common\Form\QaForm;
use Common\Service\Qa\Custom\Common\IsValidBasedWarningAdder;
use Common\Service\Qa\Custom\Common\WarningAdder;
use Common\Service\Qa\IsValidHandlerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;

/**
 * IsValidBasedWarningAdderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IsValidBasedWarningAdderTest extends MockeryTestCase
{
    public const WARNING_KEY = 'warning.key';

    public const PRIORITY = 25;

    private $qaForm;

    private $warningAdder;

    private $isValidHandler;

    private $isValidBasedWarningAdder;

    #[\Override]
    protected function setUp(): void
    {
        $this->qaForm = m::mock(QaForm::class);

        $this->warningAdder = m::mock(WarningAdder::class);

        $this->isValidHandler = m::mock(IsValidHandlerInterface::class);

        $this->isValidBasedWarningAdder = new IsValidBasedWarningAdder($this->warningAdder);
    }

    public function testSetDataWrongDataValues(): void
    {
        $this->isValidHandler->shouldReceive('isValid')
            ->andReturn(true);
        $this->assertNull(
            $this->isValidBasedWarningAdder->add($this->isValidHandler, $this->qaForm, self::WARNING_KEY, self::PRIORITY)
        );
    }

    public function testSetDataModifyForm(): void
    {
        $this->isValidHandler->shouldReceive('isValid')
            ->andReturn(false);

        $warningVisibleElement = m::mock(Hidden::class);
        $warningVisibleElement->shouldReceive('setValue')
            ->with(1)
            ->once();

        $questionFieldset = m::mock(Fieldset::class);
        $questionFieldset->shouldReceive('get')
            ->with('warningVisible')
            ->andReturn($warningVisibleElement);

        $this->warningAdder->shouldReceive('add')
            ->with($questionFieldset, self::WARNING_KEY, self::PRIORITY)
            ->once();

        $this->qaForm->shouldReceive('getQuestionFieldset')
            ->andReturn($questionFieldset);

        $this->isValidBasedWarningAdder->add($this->isValidHandler, $this->qaForm, self::WARNING_KEY, self::PRIORITY);
    }
}
