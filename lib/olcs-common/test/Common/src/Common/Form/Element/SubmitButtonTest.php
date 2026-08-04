<?php

declare(strict_types=1);

namespace CommonTest\Form\Element;

use Common\Test\MockeryTestCase;
use Common\Form\Element\Button;
use Common\Form\Element\SubmitButton;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Form\Element\SubmitButton::class)]
final class SubmitButtonTest extends MockeryTestCase
{
    protected const string TYPE_ATTRIBUTE = 'type';

    protected const string A_BUTTON_NAME = 'A BUTTON NAME';

    protected const string A_BUTTON_LABEL = 'A BUTTON LABEL';

    /**
     * @var Button|null
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructSetsTypeAttributeToSubmit(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertEquals(Button::SUBMIT, $this->sut->getAttribute(static::TYPE_ATTRIBUTE));
    }

    protected function setUpSut(...$args): void
    {
        $this->sut = new SubmitButton(...$args);
    }
}
