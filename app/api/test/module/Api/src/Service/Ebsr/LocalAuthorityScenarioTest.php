<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use Dvsa\Olcs\Api\Service\Ebsr\LocalAuthorityResolver;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\LocalAuthorityMissing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * What an EBSR file's circulated authority names and stop ATCO codes resolve to, and whether
 * the stops check passes, once old authorities are linked to the ones that replaced them.
 */
final class LocalAuthorityScenarioTest extends TestCase
{
    private LocalAuthorityDirectory $directory;

    #[\Override]
    protected function setUp(): void
    {
        $this->directory = LocalAuthorityDirectory::withDefaultAuthorities();
    }

    /**
     * @param string[] $names the file's CirculatedAuthorities
     * @param string[] $stops the file's stop ATCO codes (first three characters)
     * @param string[] $expected txc names of the authorities the registration is attached to
     */
    #[DataProvider('scenarioProvider')]
    public function testScenario(array $names, array $stops, array $expected, bool $valid): void
    {
        $fromStops = $this->directory->findByNaptanCodes($stops);
        $circulated = (new LocalAuthorityResolver())->resolveCirculated(
            $this->directory->findByTxcNames(LocalAuthorityResolver::normaliseNames($names)),
            $fromStops
        );

        $this->assertSame(
            $expected,
            array_map(static fn (LocalAuthority $authority) => $authority->getTxcName(), $circulated->getValues())
        );

        $isValid = (new LocalAuthorityMissing())->isValid([
            'localAuthoritys' => $circulated,
            'naptanAuthorities' => new ArrayCollection($fromStops),
        ]);

        $this->assertSame($valid, $isValid);
    }

    public static function scenarioProvider(): array
    {
        return [
            'new combined authority name with its old stop code' => [['GreaterManchesterMCA'], ['180'], ['GreaterManchesterMCA'], true],
            'old name with its old stop code' => [['Darlington'], ['076'], ['TeesValleyCA'], true],
            'new name with an old stop code' => [['TeesValleyCA'], ['076'], ['TeesValleyCA'], true],
            'old and new names mixed' => [
                ['Darlington', 'StocktonOnTees', 'TeesValleyCA'],
                ['076', '077'],
                ['TeesValleyCA'],
                true,
            ],
            'old name in different case' => [['gmpte'], ['180'], ['GreaterManchesterMCA'], true],
            'name with stray whitespace' => [[' Darlington '], ['076'], ['TeesValleyCA'], true],
            'old split name, stops in North Northamptonshire' => [['Northamptonshire'], ['305'], ['NorthNorthamptonshire'], true],
            'old split name, stops in West Northamptonshire' => [['Northamptonshire'], ['300'], ['WestNorthamptonshire'], true],
            'old split name, stops in both' => [
                ['Northamptonshire'],
                ['300', '305'],
                ['WestNorthamptonshire', 'NorthNorthamptonshire'],
                true,
            ],
            'new split name that misses the stops' => [['WestNorthamptonshire'], ['305'], ['WestNorthamptonshire'], false],
            'old Cornwall and Scilly name' => [['CornwallAndScillies'], ['080'], ['Cornwall'], true],
            'new Cornwall name with the old stop code' => [['Cornwall'], ['080'], ['Cornwall'], true],
            'unknown name' => [['Atlantis'], ['076'], [], false],
            'unknown stop code is ignored' => [['Kent'], ['999', '240'], ['Kent'], true],
            'shared code needs both authorities, as today' => [['CheshireEast'], ['060'], ['CheshireEast'], false],
            'shared code with both authorities' => [['Cheshire', 'CheshireEast'], ['060'], ['Cheshire', 'CheshireEast'], true],
        ];
    }
}
