<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ProcessedData;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LaEntity;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\LocalAuthorityMissing;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class LocalAuthorityMissingTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ProcessedData
 */
class LocalAuthorityMissingTest extends MockeryTestCase
{
    /**
     * tests whether missing local authorities not required are correctly identified
     *
     *
     * @param ArrayCollection $la
     * @param ArrayCollection $naptan
     * @param $valid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid(mixed $la, mixed $naptan, mixed $valid): void
    {
        $sut = new LocalAuthorityMissing();

        $value = [
            'localAuthoritys' => $la,
            'naptanAuthorities' => $naptan
        ];

        $this->assertEquals($valid, $sut->isValid($value));
    }

    /**
     * Provider for testIsValid
     *
     * @return array
     */
    public static function isValidProvider(): array
    {
        $la1 = m::mock(LaEntity::class)->makePartial();
        $la1->setId(1);

        $la2 = m::mock(LaEntity::class)->makePartial();
        $la2->setId(2);

        $la3 = m::mock(LaEntity::class)->makePartial();
        $la3->setId(3);

        return [
            [new ArrayCollection([$la3, $la2]), new ArrayCollection([$la1, $la2]), false],
            [new ArrayCollection([$la3, $la2]), new ArrayCollection([$la3]), true],
            [new ArrayCollection([$la3]), new ArrayCollection([$la3]), true],
        ];
    }
}
