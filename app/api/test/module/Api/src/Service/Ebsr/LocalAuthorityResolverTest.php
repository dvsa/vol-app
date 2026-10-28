<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr;

use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use Dvsa\Olcs\Api\Service\Ebsr\LocalAuthorityResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LocalAuthorityResolverTest extends TestCase
{
    /** @var array<string, LocalAuthority> */
    private array $authorities;

    #[\Override]
    protected function setUp(): void
    {
        $this->authorities = [];
        $names = [
            'Kent',
            'Darlington',
            'StocktonOnTees',
            'TeesValleyCA',
            'Northamptonshire',
            'WestNorthamptonshire',
            'NorthNorthamptonshire',
        ];

        foreach ($names as $name) {
            $authority = new LocalAuthority();
            $authority->setTxcName($name);
            $authority->setDescription($name);
            $this->authorities[$name] = $authority;
        }

        $this->authorities['Darlington']->addSuccessors($this->authorities['TeesValleyCA']);
        $this->authorities['StocktonOnTees']->addSuccessors($this->authorities['TeesValleyCA']);
        $this->authorities['Northamptonshire']->addSuccessors($this->authorities['WestNorthamptonshire']);
        $this->authorities['Northamptonshire']->addSuccessors($this->authorities['NorthNorthamptonshire']);
    }

    /**
     * @param string[] $circulated records the file's authority names matched
     * @param string[] $fromStops records the file's stop codes matched
     * @param string[] $expected
     */
    #[DataProvider('resolveCirculatedProvider')]
    public function testResolveCirculated(array $circulated, array $fromStops, array $expected): void
    {
        $resolved = (new LocalAuthorityResolver())->resolveCirculated(
            $this->pick($circulated),
            $this->pick($fromStops)
        );

        $this->assertSame($this->pick($expected), $resolved->getValues());
    }

    public static function resolveCirculatedProvider(): array
    {
        return [
            'current authority is kept' => [['Kent'], [], ['Kent']],
            'old authority becomes its successor' => [['Darlington'], [], ['TeesValleyCA']],
            'old and new name for the same authority give it once' => [['Darlington', 'TeesValleyCA'], [], ['TeesValleyCA']],
            'nothing circulated' => [[], ['Kent'], []],
            'split authority narrowed to the side its stops are in' => [
                ['Northamptonshire'],
                ['NorthNorthamptonshire'],
                ['NorthNorthamptonshire'],
            ],
            'split authority keeps both sides when the stops are in both' => [
                ['Northamptonshire'],
                ['WestNorthamptonshire', 'NorthNorthamptonshire'],
                ['WestNorthamptonshire', 'NorthNorthamptonshire'],
            ],
            'split authority keeps both sides when the stops do not say' => [
                ['Northamptonshire'],
                ['Kent'],
                ['WestNorthamptonshire', 'NorthNorthamptonshire'],
            ],
        ];
    }

    /**
     * A combined authority has one traffic area but can cover councils in several, so the
     * registration takes its traffic areas from the councils involved
     *
     * @param string[] $circulated records the file's authority names matched
     * @param string[] $fromStops records the file's stop codes matched
     * @param string[] $expected
     */
    #[DataProvider('trafficAreaAuthoritiesProvider')]
    public function testTrafficAreaAuthorities(array $circulated, array $fromStops, array $expected): void
    {
        $this->assertSame(
            $this->pick($expected),
            (new LocalAuthorityResolver())->trafficAreaAuthorities($this->pick($circulated), $this->pick($fromStops))
        );
    }

    public static function trafficAreaAuthoritiesProvider(): array
    {
        return [
            'a named council gives its own' => [['Darlington'], ['Darlington'], ['Darlington']],
            'a named current authority gives its own when no stop is in it' => [['Kent'], [], ['Kent']],
            'a named current authority gives its own stops\' councils' => [['Kent'], ['Kent'], ['Kent']],
            'a named combined authority gives the councils its stops are in' => [
                ['TeesValleyCA'],
                ['StocktonOnTees', 'Kent'],
                ['StocktonOnTees'],
            ],
            'a named combined authority with no stops in it gives its own' => [['TeesValleyCA'], ['Kent'], ['TeesValleyCA']],
            'each council once' => [['Darlington', 'TeesValleyCA'], ['Darlington'], ['Darlington']],
        ];
    }

    /**
     * @param array<int, mixed> $names
     * @param string[] $expected
     */
    #[DataProvider('normaliseNamesProvider')]
    public function testNormaliseNames(array $names, array $expected): void
    {
        $this->assertSame($expected, LocalAuthorityResolver::normaliseNames($names));
    }

    public static function normaliseNamesProvider(): array
    {
        return [
            'surrounding whitespace removed' => [[' Leicester', "Kent\n"], ['Leicester', 'Kent']],
            'duplicates removed' => [['Kent', 'Kent ', 'Surrey'], ['Kent', 'Surrey']],
            'empty names removed' => [['', '  ', 'Kent'], ['Kent']],
            'nothing' => [[], []],
        ];
    }

    /**
     * @param string[] $names
     * @return LocalAuthority[]
     */
    private function pick(array $names): array
    {
        return array_map(fn (string $name) => $this->authorities[$name], $names);
    }
}
