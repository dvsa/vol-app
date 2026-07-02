<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\Custom\Ecmt\YesNoRadio;
use DMS\PHPUnitExtensions\ArraySubset\Assert;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * YesNoRadioTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class YesNoRadioTest extends MockeryTestCase
{
    private $yesNoRadio;

    #[\Override]
    public function setUp(): void
    {
        $this->yesNoRadio = new YesNoRadio();
    }

    public function testAttributes(): void
    {
        $expectedAttributes = [
            'id' => 'yesNoRadio',
            'radios_wrapper_attributes' => [
                'id' => 'yesNoRadio',
                'class' => 'govuk-radios--conditional',
                'data-module' => 'radios',
            ]
        ];

        $this->assertEquals(
            $expectedAttributes,
            $this->yesNoRadio->getAttributes()
        );
    }

    public function testSetStandardValueOptions(): void
    {
        $expectedValueOptions = [
            'yes' => [
                'label' => 'Yes',
                'value' => 'Y',
                'attributes' => [
                    'data-aria-controls' => 'RestrictedCountriesList',
                ],
            ],
            'no' => [
                'label' => 'No',
                'value' => 'N',
            ]
        ];

        $this->yesNoRadio->setStandardValueOptions();

        $this->assertArraySubsetRecursive(
            $expectedValueOptions,
            $this->yesNoRadio->getValueOptions()
        );
    }

    private function assertArraySubsetRecursive(array $subset, $array): void
    {
        foreach ($subset as $key => $value) {
            $this->assertArrayHasKey($key, $array);
            if (is_array($value)) {
                $this->assertArraySubsetRecursive($value, $array[$key]);
            } else {
                $this->assertEquals($value, $array[$key]);
            }
        }
    }
}
