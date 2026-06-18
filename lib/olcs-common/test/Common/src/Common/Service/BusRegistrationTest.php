<?php

namespace CommonTest\Service;

use Common\Service\BusRegistration;

/**
 * Class BusRegistrationTest
 * @package CommonTest\Service
 */
class BusRegistrationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @psalm-return array{shortNotice: mixed,...}
     */
    public function testCreateNew(): array
    {
        $licence = ['id' => 123, 'licNo' => 'AB12563'];

        $sut = new BusRegistration();
        $result = $sut->createNew($licence);

        $this->assertIsArray($result);
        $this->assertSame($licence['id'], $result['licence']['id']);

        $this->assertEquals(BusRegistration::STATUS_NEW, $result['status']);
        $this->assertEquals(BusRegistration::STATUS_NEW, $result['revertStatus']);

        $this->assertArrayHasKey('shortNotice', $result);
        $this->assertNotEmpty($result['shortNotice']);

        return $result;
    }

    /**
     * @depends testCreateNew
     * @param $new
     * @return array
     */
    public function testCreateVariation($new)
    {
        $new['otherServices'][] = ['id' => 45, 'busRegId' => 17, 'serviceNo' => '4a'];
        $new['trcConditionChecked'] = 'Y';
        $new['id'] = 17;

        $mostRecent = [];
        $mostRecent['variationNo'] = 3;

        $sut = new BusRegistration();
        $result = $sut->createVariation($new, $mostRecent);

        $this->assertEquals(4, $result['variationNo']);
        $this->assertEquals(BusRegistration::STATUS_VAR, $result['status']);
        $this->assertEquals(BusRegistration::STATUS_VAR, $result['revertStatus']);

        $this->assertSame($new['id'], $result['parent']['id']);

        $this->assertEquals('N', $result['trcConditionChecked']);
        $this->assertEquals(['serviceNo' => '4a'], $result['otherServices'][0]);

        $this->assertArrayNotHasKey('id', $result);

        return $result;
    }

    /**
     * @depends testCreateVariation
     * @param $variation
     */
    public function testCreateCancellation($variation): void
    {
        $mostRecent = ['variationNo' => $variation['variationNo']];

        $variation['id'] = 18;
        $sut = new BusRegistration();
        $result = $sut->createCancellation($variation, $mostRecent);

        $this->assertEquals(5, $result['variationNo']);
        $this->assertEquals(BusRegistration::STATUS_CANCEL, $result['status']);
        $this->assertEquals(BusRegistration::STATUS_CANCEL, $result['revertStatus']);
    }

    public function testGetCascadeOptions(): void
    {
        $sut = new BusRegistration();
        $options = $sut->getCascadeOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('cascade', $options);
        $this->assertArrayHasKey('single', $options['cascade']);
        $this->assertNotEmpty($options['cascade']['single']);
    }

    public function testGetCascadeOptionsVariation(): void
    {
        $sut = new BusRegistration();
        $options = $sut->getCascadeOptionsVariation();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('cascade', $options);
        $this->assertArrayHasKey('single', $options['cascade']);
        $this->assertNotEmpty($options['cascade']['single']);
        $this->assertArrayHasKey('list', $options['cascade']);
        $this->assertNotEmpty($options['cascade']['list']);
    }
}
