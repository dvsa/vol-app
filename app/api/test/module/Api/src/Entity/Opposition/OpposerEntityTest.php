<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Opposition;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Opposition\Opposer as Entity;
use Dvsa\Olcs\Api\Entity\Opposition\Opposition;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Opposition\Opposer
 * @covers Dvsa\Olcs\Api\Entity\Opposition\AbstractOpposer
 */
class OpposerEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /** @var  ContactDetails */
    private $mockCd;
    private $opposerType;
    private $oppositionType;

    /** @var  Entity */
    private $sut;

    public function setUp(): void
    {
        $this->mockCd = m::mock(ContactDetails::class);
        $this->opposerType = new RefData('OPPOSER_TYPE');
        $this->oppositionType = new RefData(Opposition::OPPOSITION_TYPE_ENV);

        $this->sut = new Entity($this->mockCd, $this->opposerType, $this->oppositionType);
    }

    public function testConstructor(): void
    {
        static::assertSame($this->mockCd, $this->sut->getContactDetails());
        static::assertSame($this->opposerType, $this->sut->getOpposerType());
    }

    public function testUpdateOk(): void
    {
        $opposerType = new RefData('OPPOSER_TYPE_2');

        $this->sut->update(
            [
                'opposerType' => $opposerType,
                'oppositionType' => $this->oppositionType,
            ]
        );

        static::assertSame($opposerType, $this->sut->getOpposerType());
    }

    public function testUpdateException(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->sut->update(
            [
                'opposerType' => new RefData(),
                'oppositionType' => $this->oppositionType,
            ]
        );
    }
}
