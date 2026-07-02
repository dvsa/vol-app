<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use DMS\PHPUnitExtensions\ArraySubset\Assert;
use Common\Service\Qa\Custom\Ecmt\RestrictedCountriesMultiCheckboxFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element\MultiCheckbox;

/**
 * RestrictedCountriesMultiCheckboxFactoryTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RestrictedCountriesMultiCheckboxFactoryTest extends MockeryTestCase
{
    public function testCreate(): void
    {
        $name = 'yesContent';

        $expectedOptions = [
            'label' => 'markup-ecmt-restricted-countries-list-label',
            'label_attributes' => [
                'class' => 'form-control form-control--checkbox'
            ]
        ];

        $expectedAttributes = [
            'class' => 'input--trips',
            'id' => 'RestrictedCountriesList',
            'allowWrap' => true,
            'data-container-class' => 'form-control__container',
            'aria-label' => 'permits.page.restricted-countries.hint'
        ];

        $restrictedCountriesMultiCheckboxFactory = new RestrictedCountriesMultiCheckboxFactory();
        $multiCheckbox = $restrictedCountriesMultiCheckboxFactory->create($name);

        $this->assertInstanceOf(MultiCheckbox::class, $multiCheckbox);
        $this->assertEquals($name, $multiCheckbox->getName());
        $this->assertArraySubsetRecursive($expectedOptions, $multiCheckbox->getOptions());
        $this->assertArraySubsetRecursive($expectedAttributes, $multiCheckbox->getAttributes());
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
