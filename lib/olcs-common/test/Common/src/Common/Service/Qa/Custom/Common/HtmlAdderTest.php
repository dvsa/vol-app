<?php

namespace CommonTest\Service\Qa\Custom\Common;

use Common\Form\Elements\Types\Html;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;

/**
 * HtmlAdderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class HtmlAdderTest extends MockeryTestCase
{
    public const ELEMENT_NAME = 'elementName';

    public const MARKUP = '<h1>markup</h1>';

    public const EXPECTED_PARAMS = [
        'name' => self::ELEMENT_NAME,
        'type' => Html::class,
        'attributes' => [
            'value' => self::MARKUP
        ]
    ];

    private $fieldset;

    #[\Override]
    protected function setUp(): void
    {
        $this->fieldset = m::mock(Fieldset::class);
    }

    public function testAddWithoutPriority(): void
    {
        $expectedFlags = [];

        $this->fieldset->shouldReceive('add')
            ->with(self::EXPECTED_PARAMS, $expectedFlags)
            ->once();

        $htmlAdder = new HtmlAdder();
        $htmlAdder->add($this->fieldset, self::ELEMENT_NAME, self::MARKUP);
    }

    public function testAddWithPriority(): void
    {
        $priority = -100;

        $expectedFlags = ['priority' => $priority];

        $this->fieldset->shouldReceive('add')
            ->with(self::EXPECTED_PARAMS, $expectedFlags)
            ->once();

        $htmlAdder = new HtmlAdder();
        $htmlAdder->add($this->fieldset, self::ELEMENT_NAME, self::MARKUP, $priority);
    }
}
