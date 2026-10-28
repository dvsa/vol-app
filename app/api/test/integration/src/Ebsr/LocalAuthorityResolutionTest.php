<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Ebsr;

use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use Dvsa\Olcs\Api\Service\Ebsr\LocalAuthorityResolver;
use Dvsa\OlcsTest\Integration\IntegrationTestCase;

/**
 * Every authority name an EBSR file can carry, and every NaPTAN ATCO area code a stop can start
 * with, resolved against the real local_authority data to the authority that stands for it now.
 *
 * The baseline is the signed-off mapping in readable form: a name or code that resolves
 * differently from it fails the test. It changes when the data does (an olcs-etl patch) or when
 * DfT changes the authority list (a new schema) or NaPTAN changes its codes (the code list).
 * After an intended change, regenerate it and review the diff:
 *
 *   REGENERATE_LOCAL_AUTHORITY_BASELINE=1 vendor/bin/phpunit \
 *     -c phpunit-integration.xml --filter LocalAuthorityResolutionTest
 */
class LocalAuthorityResolutionTest extends IntegrationTestCase
{
    private const string BASELINE_FILE = __DIR__ . '/../../local-authority-resolution-baseline.txt';

    /**
     * NaPTAN's ATCO area codes in use, one per line (https://beta-naptan.dft.gov.uk/article/atco-codes-in-use)
     */
    private const string NAPTAN_CODES_FILE = __DIR__ . '/../../naptan-atco-area-codes.txt';

    private const string XSD_DIR = __DIR__ . '/../../../../module/Api/data/ebsr/xsd';

    public function testNamesAndCodesResolveAsTheBaselineSays(): void
    {
        $lines = array_merge(
            ['# TransXChange authority name -> current authority'],
            $this->resolveNames(),
            ['', '# NaPTAN ATCO area code -> current authority'],
            $this->resolveCodes(),
        );
        $actual = implode("\n", $lines) . "\n";

        if (getenv('REGENERATE_LOCAL_AUTHORITY_BASELINE')) {
            file_put_contents(self::BASELINE_FILE, $actual);
            $this->assertFileExists(self::BASELINE_FILE);
            return;
        }

        $this->assertFileExists(self::BASELINE_FILE, 'Generate the baseline with REGENERATE_LOCAL_AUTHORITY_BASELINE=1');
        $this->assertSame(file_get_contents(self::BASELINE_FILE), $actual);
    }

    /**
     * Successor links lead straight to authorities that are current, so nothing depends on
     * following a chain, and no two current authorities answer to the same name
     */
    public function testSuccessorLinksPointAtCurrentAuthorities(): void
    {
        /** @var LocalAuthority[] $authorities */
        $authorities = $this->em()->getRepository(LocalAuthority::class)->findAll();

        $chained = [];
        $currentNames = [];

        foreach ($authorities as $authority) {
            foreach ($authority->getSuccessors() as $successor) {
                if ($successor->isSuperseded()) {
                    $chained[] = $authority->getTxcName() . ' -> ' . $successor->getTxcName();
                }
            }

            if (!$authority->isSuperseded() && (string) $authority->getTxcName() !== '') {
                $currentNames[] = strtolower((string) $authority->getTxcName());
            }
        }

        $this->assertSame([], $chained, 'Successors that have themselves been replaced');
        $this->assertSame(
            [],
            array_keys(array_filter(array_count_values($currentNames), static fn (int $count) => $count > 1)),
            'TransXChange names held by more than one current authority'
        );
    }

    /**
     * @return string[]
     */
    private function resolveNames(): array
    {
        $resolver = new LocalAuthorityResolver();
        $lines = [];

        foreach ($this->schemaAuthorityNames() as $name) {
            $lines[] = $name . ' -> ' . $this->describe(
                $resolver->resolveCirculated($this->repo('LocalAuthority')->fetchByTxcName([$name]), [])->getValues()
            );
        }

        return $lines;
    }

    /**
     * @return string[]
     */
    private function resolveCodes(): array
    {
        $codes = array_filter(
            array_map(trim(...), file(self::NAPTAN_CODES_FILE, FILE_IGNORE_NEW_LINES)),
            static fn (string $line) => $line !== '' && !str_starts_with($line, '#')
        );

        $lines = [];

        foreach ($codes as $code) {
            $current = [];

            /** @var LocalAuthority $authority */
            foreach ($this->repo('LocalAuthority')->fetchByNaptan([$code]) as $authority) {
                $current[] = $this->describe($authority->getCurrentAuthorities());
            }

            $lines[] = $code . ' -> ' . ($current === [] ? '(none)' : implode(' AND ', $current));
        }

        return $lines;
    }

    /**
     * Every AuthorityNameEnumeration value in every schema version VOL holds
     *
     * @return string[]
     */
    private function schemaAuthorityNames(): array
    {
        $names = [];

        foreach (glob(self::XSD_DIR . '/TransXChange_schema_*/txc/TXC_authorities-*.xsd') as $xsd) {
            $document = new \DOMDocument();
            $document->load($xsd);

            $xpath = new \DOMXPath($document);
            $xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema');

            $query = "//xsd:simpleType[@name='AuthorityNameEnumeration']//xsd:enumeration/@value";
            foreach ($xpath->query($query) as $value) {
                $names[$value->nodeValue] = $value->nodeValue;
            }
        }

        $this->assertNotEmpty($names, 'No authority names found in the bundled schemas');

        uksort($names, strcasecmp(...));

        return array_values($names);
    }

    /**
     * @param LocalAuthority[] $authorities
     */
    private function describe(array $authorities): string
    {
        if ($authorities === []) {
            return '(none)';
        }

        return implode(' + ', array_map(
            static fn (LocalAuthority $authority) => sprintf('%s [%d]', $authority->getTxcName(), $authority->getId()),
            $authorities
        ));
    }
}
