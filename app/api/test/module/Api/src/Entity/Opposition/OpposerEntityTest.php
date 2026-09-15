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

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Opposition\Opposer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Opposition\AbstractOpposer::class)]
final class OpposerEntityTest extends EntityTester
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

    #[\Override]
    public function setUp(): void
    {
        $this->mockCd = m::mock(ContactDetails::class);
        $this->opposerType = new RefData('OPPOSER_TYPE');
        $this->oppositionType = new RefData(Opposition::OPPOSITION_TYPE_ENV);

        $this->sut = new Entity($this->mockCd, $this->opposerType, $this->oppositionType);
    }

    public function testConstructor(): void
    {
        $this->assertSame($this->mockCd, $this->sut->getContactDetails());
        $this->assertSame($this->opposerType, $this->sut->getOpposerType());
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

        $this->assertSame($opposerType, $this->sut->getOpposerType());
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
