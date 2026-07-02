<?php

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Types\PostcodeSearch;
use DMS\PHPUnitExtensions\ArraySubset\Assert;
use Laminas\Form\Element\Text;

/**
 * PostcodeTest
 */
class PostcodeTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorPostcodeElement(): void
    {
        $sut = new PostcodeSearch('foo');

        $postcodeElement = $sut->get('postcode');
        $this->assertInstanceOf(Text::class, $postcodeElement);

        $attributes = $postcodeElement->getAttributes();
        $this->assertArraySubsetRecursive(
            [
                'class' => 'short js-input',
                'data-container-class' => 'inline',
            ],
            $attributes
        );
        $this->assertMatchesRegularExpression('/postcodeInput[0-9]/', $attributes['id']);
    }

    public function testConstructorPostcodeElementNumberIsIncremented(): void
    {
        $sut1 = new PostcodeSearch('foo');
        $sut2 = new PostcodeSearch('bar');

        $this->assertNotEquals(
            $sut1->get('postcode')->getAttributes()['id'],
            $sut2->get('postcode')->getAttributes()['id']
        );
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
