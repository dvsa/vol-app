<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr;

use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;

/**
 * In-memory local authority records for EBSR tests, looked up the way the LocalAuthority
 * repository does it (fetchByTxcName is case-insensitive, fetchByNaptan matches the code).
 *
 * The default records mirror the shape of the real data (old rows keep their names and codes,
 * new rows are linked from them) without depending on it; the integration suite checks the
 * real mapping.
 */
final class LocalAuthorityDirectory
{
    /** @var array<string, LocalAuthority> keyed by txc name */
    private array $authorities = [];

    public static function withDefaultAuthorities(): self
    {
        $directory = new self();

        $directory->add('Darlington', 'Tees Valley Combined Authority (formally DBC)', '076');
        $directory->add('StocktonOnTees', 'Tees Valley Combined Authority (formally Stockton)', '077');
        $directory->add('TeesValleyCA', 'Tees Valley Combined Authority');
        $directory->link('Darlington', 'TeesValleyCA');
        $directory->link('StocktonOnTees', 'TeesValleyCA');

        $directory->add('GMPTE', 'Transport for Greater Manchester (TFGM)', '180');
        $directory->add('GreaterManchesterMCA', 'Greater Manchester Combined Authority');
        $directory->link('GMPTE', 'GreaterManchesterMCA');

        // NaPTAN split Northamptonshire in 2024: 300 stayed with West, North got 305
        $directory->add('Northamptonshire', 'Northamptonshire County Council');
        $directory->add('WestNorthamptonshire', 'West Northamptonshire Council', '300');
        $directory->add('NorthNorthamptonshire', 'North Northamptonshire Council', '305');
        $directory->link('Northamptonshire', 'WestNorthamptonshire');
        $directory->link('Northamptonshire', 'NorthNorthamptonshire');

        // Scilly has no ATCO code of its own; its stops are under Cornwall's 080
        $directory->add('CornwallAndScillies', 'Cornwall Council', '080');
        $directory->add('Cornwall', 'Cornwall Council');
        $directory->add('Scillies', 'Council of the Isles of Scilly');
        $directory->link('CornwallAndScillies', 'Cornwall');

        // Two records share 060 and neither is linked yet: today's behaviour, both required
        $directory->add('Cheshire', 'Cheshire West & Chester Council', '060');
        $directory->add('CheshireEast', 'Cheshire East Council', '060');

        $directory->add('Kent', 'Kent County Council', '240');
        $directory->add('Leicestershire', 'Leicestershire County Council', '260');

        return $directory;
    }

    public function add(string $txcName, string $description, ?string $naptanCode = null): LocalAuthority
    {
        $authority = new LocalAuthority();
        $authority->setTxcName($txcName);
        $authority->setDescription($description);
        $authority->setNaptanCode($naptanCode);

        return $this->authorities[$txcName] = $authority;
    }

    public function link(string $predecessor, string $successor): void
    {
        $this->authorities[$predecessor]->addSuccessors($this->authorities[$successor]);
    }

    /**
     * @param string[] $names
     *
     * @return LocalAuthority[]
     */
    public function findByTxcNames(array $names): array
    {
        $names = array_map(strtolower(...), $names);

        return array_values(array_filter(
            $this->authorities,
            static fn (LocalAuthority $authority) => in_array(strtolower((string) $authority->getTxcName()), $names, true)
        ));
    }

    /**
     * @param string[] $codes
     *
     * @return LocalAuthority[]
     */
    public function findByNaptanCodes(array $codes): array
    {
        return array_values(array_filter(
            $this->authorities,
            static fn (LocalAuthority $authority) => in_array($authority->getNaptanCode(), $codes, true)
        ));
    }
}
