<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\LicenceOperatingCentre as Repo;
use Dvsa\Olcs\Api\Entity\Cases\Complaint;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre as Entity;
use Dvsa\Olcs\Transfer\Query\Licence\OperatingCentres as Qry;

final class LicenceOperatingCentreTest extends RepositoryTestCase
{
    private const string ADR_SELECT = "concat(ifnull(oca.addressLine1,''),ifnull(oca.addressLine2,''),"
        . "ifnull(oca.addressLine3,''),ifnull(oca.addressLine4,''),ifnull(oca.town,'')) as adr";

    private const string JOINS = ' LEFT JOIN loc.s4 s4 INNER JOIN loc.operatingCentre oc'
        . ' INNER JOIN oc.address oca LEFT JOIN oca.countryCode ocac'
        . ' LEFT JOIN oc.complaints occ WITH occ.status = :complaintStatus';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchByLicence(): void
    {
        $qb = $this->createRealQb()->willReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByLicence(7634));

        // LicenceOperatingCentre has no RefData associations, so withRefdata() adds nothing.
        $this->assertSame(
            'SELECT loc, oc, w0 FROM ' . Entity::class . ' loc'
            . ' LEFT JOIN loc.operatingCentre oc LEFT JOIN oc.address w0'
            . ' WHERE loc.licence = :licenceId',
            $qb->getDQL(),
        );
        $this->assertSame(7634, $qb->getParameter('licenceId')->getValue());
    }

    public function testFetchByLicenceIdForOperatingCentres(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getArrayResult')->andReturn([['foo']]);

        $this->assertSame([['foo']], $this->sut->fetchByLicenceIdForOperatingCentres(111));

        $this->assertSame(
            'SELECT loc, s4, oc, oca, ocac, occ FROM ' . Entity::class . ' loc' . self::JOINS
            . ' WHERE loc.licence = :licence'
            . ' ORDER BY oca.id ASC',
            $qb->getDQL(),
        );
        $this->assertSame(111, $qb->getParameter('licence')->getValue());
        $this->assertSame(Complaint::COMPLAIN_STATUS_OPEN, $qb->getParameter('complaintStatus')->getValue());
    }

    /**
     * A sort turns on the concatenated address column, which is registered as a composite
     * field so the ORDER BY uses the alias directly rather than prefixing it with the root.
     */
    public function testFetchByLicenceIdForOperatingCentresWithQuery(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getArrayResult')->andReturn([['foo' => 'bar']]);

        $result = $this->sut->fetchByLicenceIdForOperatingCentres(
            1,
            Qry::create(['sort' => 'adr', 'order' => 'ASC']),
        );

        $this->assertSame([['foo' => 'bar']], $result);

        $this->assertSame(
            'SELECT loc, s4, oc, oca, ocac, occ, ' . self::ADR_SELECT
            . ' FROM ' . Entity::class . ' loc' . self::JOINS
            . ' WHERE loc.licence = :licence'
            . ' ORDER BY adr ASC',
            $qb->getDQL(),
        );
    }

    public function testMaybeRemoveAdrColumn(): void
    {
        $data = [
            [0 => ['operatingCentre' => 'foo'], 'adr' => 'bar'],
            [0 => ['operatingCentre' => 'cake'], 'adr' => 'baz'],
            ['operatingCentre' => 'baz'],
        ];

        $this->assertSame(
            [
                ['operatingCentre' => 'foo'],
                ['operatingCentre' => 'cake'],
                ['operatingCentre' => 'baz'],
            ],
            $this->sut->maybeRemoveAdrColumn($data),
        );
    }
}
