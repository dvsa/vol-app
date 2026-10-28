<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ProcessedData;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LaEntity;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\LocalAuthorityMissing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class LocalAuthorityMissingTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ProcessedData
 */
final class LocalAuthorityMissingTest extends TestCase
{
    /**
     * @param string[] $circulated authorities the file circulates (already resolved to current ones)
     * @param string[] $fromStops authority records the stops' ATCO codes matched
     */
    #[DataProvider('isValidProvider')]
    public function testIsValid(array $circulated, array $fromStops, bool $valid, array $messages = []): void
    {
        $authorities = $this->authorities();

        $value = [
            'localAuthoritys' => new ArrayCollection(array_map(static fn (string $name) => $authorities[$name], $circulated)),
            'naptanAuthorities' => new ArrayCollection(array_map(static fn (string $name) => $authorities[$name], $fromStops)),
        ];

        $sut = new LocalAuthorityMissing();

        $this->assertSame($valid, $sut->isValid($value));
        $this->assertSame($messages, array_values($sut->getMessages()));
    }

    public static function isValidProvider(): array
    {
        $missing = 'According to the stops, these Local Authorities are missing from the data: ';

        return [
            'stop authority not circulated' => [['Kent', 'Surrey'], ['Essex', 'Surrey'], false, [$missing . 'Essex County Council']],
            'stop authority circulated with another' => [['Kent', 'Surrey'], ['Kent'], true],
            'stop authority circulated' => [['Kent'], ['Kent'], true],
            'no stops' => [['Kent'], [], true],
            'old stop authority covered by its successor' => [['TeesValleyCA'], ['Darlington'], true],
            'old stop authorities reported once by successor' => [
                ['Kent'],
                ['Darlington', 'StocktonOnTees'],
                false,
                [$missing . 'Tees Valley Combined Authority'],
            ],
            'split stop authority covered by either successor' => [['NorthNorthamptonshire'], ['Northamptonshire'], true],
            'split stop authority names every successor when missing' => [
                ['Kent'],
                ['Northamptonshire'],
                false,
                [$missing . 'West Northamptonshire Council or North Northamptonshire Council'],
            ],
            // Cheshire and CheshireEast both hold ATCO 060 and have no successor: as today, both are required
            'shared code without successors needs every authority' => [
                ['CheshireEast'],
                ['Cheshire', 'CheshireEast'],
                false,
                [$missing . 'Cheshire West & Chester Council'],
            ],
            'shared code without successors with every authority' => [['Cheshire', 'CheshireEast'], ['Cheshire', 'CheshireEast'], true],
        ];
    }

    /**
     * @return array<string, LaEntity>
     */
    private function authorities(): array
    {
        $authorities = [];
        foreach (
            [
                'Kent' => 'Kent County Council',
                'Surrey' => 'Surrey County Council',
                'Essex' => 'Essex County Council',
                'Darlington' => 'Tees Valley Combined Authority (formally DBC)',
                'StocktonOnTees' => 'Tees Valley Combined Authority (formally Stockton)',
                'TeesValleyCA' => 'Tees Valley Combined Authority',
                'Northamptonshire' => 'Northamptonshire County Council',
                'WestNorthamptonshire' => 'West Northamptonshire Council',
                'NorthNorthamptonshire' => 'North Northamptonshire Council',
                'Cheshire' => 'Cheshire West & Chester Council',
                'CheshireEast' => 'Cheshire East Council',
            ] as $txcName => $description
        ) {
            $authority = new LaEntity();
            $authority->setTxcName($txcName);
            $authority->setDescription($description);
            $authorities[$txcName] = $authority;
        }

        $authorities['Darlington']->addSuccessors($authorities['TeesValleyCA']);
        $authorities['StocktonOnTees']->addSuccessors($authorities['TeesValleyCA']);
        $authorities['Northamptonshire']->addSuccessors($authorities['WestNorthamptonshire']);
        $authorities['Northamptonshire']->addSuccessors($authorities['NorthNorthamptonshire']);

        return $authorities;
    }
}
