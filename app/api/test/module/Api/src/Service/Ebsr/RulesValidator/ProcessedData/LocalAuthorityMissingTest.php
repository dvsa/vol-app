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
final class LocalAuthorityMissingTest extends MockeryTestCase
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
    public function testIsValid(array $laIds, array $naptanIds, mixed $valid): void
    {
        $sut = new LocalAuthorityMissing();

        // One instance per id: the same authority must be the same object in both collections
        $localAuthorities = [];
        foreach (array_unique(array_merge($laIds, $naptanIds)) as $id) {
            $localAuthorities[$id] = $this->createLocalAuthority($id);
        }

        $value = [
            'localAuthoritys' => new ArrayCollection(array_map(static fn (int $id) => $localAuthorities[$id], $laIds)),
            'naptanAuthorities' => new ArrayCollection(array_map(static fn (int $id) => $localAuthorities[$id], $naptanIds)),
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
        return [
            [[3, 2], [1, 2], false],
            [[3, 2], [3], true],
            [[3], [3], true],
        ];
    }

    private function createLocalAuthority(int $id): LaEntity
    {
        $la = m::mock(LaEntity::class)->makePartial();
        $la->setId($id);

        return $la;
    }
}
