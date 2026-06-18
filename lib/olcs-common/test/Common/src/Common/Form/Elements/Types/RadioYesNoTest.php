<?php

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Types\RadioYesNo;

/**
 * RadioYesNoTest
 */
class RadioYesNoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RadioYesNo
     */
    private $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new RadioYesNo();
    }

    public function testInit(): void
    {
        $this->sut->init();

        $subset = ['Y' => ['label' => 'Yes', 'value' => 'Y'], 'N' => ['label' => 'No', 'value' => 'N']];
        $actual = $this->sut->getValueOptions();
        $this->assertArraySubsetRecursive($subset, $actual);
    }

    private function assertArraySubsetRecursive(array $subset, array $array): void
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
