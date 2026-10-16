<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre as Repo;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre as ApplicationOperatingCentreEntity;
use Dvsa\Olcs\Api\Entity\Cases\Complaint;
use Dvsa\Olcs\Transfer\Query\Application\OperatingCentres as Qry;
use Mockery as m;

final class ApplicationOperatingCentreTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . ApplicationOperatingCentreEntity::class . ' aoc';

    private const string ADR_SELECT = "concat(ifnull(oca.addressLine1,''),ifnull(oca.addressLine2,''),"
        . "ifnull(oca.addressLine3,''),ifnull(oca.addressLine4,''),ifnull(oca.town,'')) as adr";

    private const string OC_JOINS = ' LEFT JOIN aoc.s4 s4 INNER JOIN aoc.operatingCentre oc'
        . ' INNER JOIN oc.address oca LEFT JOIN oca.countryCode ocac'
        . ' LEFT JOIN oc.complaints occ WITH occ.status = :complaintStatus';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchByApplication(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByApplication(12));

        // ApplicationOperatingCentre has no RefData associations.
        $this->assertSame(
            'SELECT aoc, oc, w0' . self::FROM
            . ' LEFT JOIN aoc.operatingCentre oc LEFT JOIN oc.address w0'
            . ' WHERE aoc.application = :applicationId',
            $qb->getDQL(),
        );
        $this->assertSame(12, $qb->getParameter('applicationId')->getValue());
    }

    public function testFetchByS4(): void
    {
        $qb = $this->createRealQb()->willReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByS4(12));

        $this->assertSame(
            'SELECT aoc' . self::FROM . ' WHERE aoc.s4 = :s4Id',
            $qb->getDQL(),
        );
        $this->assertSame(12, $qb->getParameter('s4Id')->getValue());
    }

    public function testFetchByApplicationOrderByAddress(): void
    {
        $qb = $this->createRealQb()->willReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByApplicationOrderByAddress(12));

        $this->assertSame(
            'SELECT aoc, oc, address' . self::FROM
            . ' LEFT JOIN aoc.operatingCentre oc LEFT JOIN oc.address address'
            . ' WHERE aoc.application = :applicationId'
            . ' ORDER BY address.town ASC',
            $qb->getDQL(),
        );
    }

    public function testFetchByApplicationIdForOperatingCentres(): void
    {
        $this->sut->initService($this->repositoryServiceManagerReturningLocRepo());

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getArrayResult')->andReturn(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $this->sut->fetchByApplicationIdForOperatingCentres(111));

        $this->assertSame(
            'SELECT aoc, s4, oc, oca, ocac, occ' . self::FROM . self::OC_JOINS
            . ' WHERE aoc.application = :application'
            . ' ORDER BY oca.id ASC',
            $qb->getDQL(),
        );
        $this->assertSame(111, $qb->getParameter('application')->getValue());
        $this->assertSame(Complaint::COMPLAIN_STATUS_OPEN, $qb->getParameter('complaintStatus')->getValue());
    }

    /**
     * A sort switches on the concatenated address column, registered as a composite field so the
     * ORDER BY uses the bare alias.
     */
    public function testFetchByApplicationIdForOperatingCentresWithQuery(): void
    {
        $this->sut->initService($this->repositoryServiceManagerReturningLocRepo());

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getArrayResult')->andReturn(['foo' => 'bar']);

        $result = $this->sut->fetchByApplicationIdForOperatingCentres(
            111,
            Qry::create(['sort' => 'adr', 'order' => 'ASC']),
        );

        $this->assertSame(['foo' => 'bar'], $result);

        $this->assertSame(
            'SELECT aoc, s4, oc, oca, ocac, occ, ' . self::ADR_SELECT . self::FROM . self::OC_JOINS
            . ' WHERE aoc.application = :application'
            . ' ORDER BY adr ASC',
            $qb->getDQL(),
        );
    }

    public function testFindCorrespondingLoc(): void
    {
        $oc = m::mock(Entity\OperatingCentre\OperatingCentre::class)->makePartial();

        $aoc = m::mock(ApplicationOperatingCentreEntity::class)->makePartial();
        $aoc->setOperatingCentre($oc);

        $loc = m::mock(Entity\Licence\LicenceOperatingCentre::class)->makePartial();
        $loc->setOperatingCentre($oc);

        $licence = m::mock(Entity\Licence\Licence::class)->makePartial();
        $licence->setOperatingCentres(new ArrayCollection([$loc]));

        $this->assertSame($loc, $this->sut->findCorrespondingLoc($aoc, $licence));
    }

    public function testFindCorrespondingLocWithoutMatch(): void
    {
        $aoc = m::mock(ApplicationOperatingCentreEntity::class)->makePartial();
        $aoc->setOperatingCentre(m::mock(Entity\OperatingCentre\OperatingCentre::class)->makePartial());

        $licence = m::mock(Entity\Licence\Licence::class)->makePartial();
        $licence->setOperatingCentres(new ArrayCollection());

        $this->assertNull($this->sut->findCorrespondingLoc($aoc, $licence));
    }

    /**
     * The adr column is stripped by the LicenceOperatingCentre repository, which this one pulls
     * in through initService().
     */
    private function repositoryServiceManagerReturningLocRepo(): RepositoryServiceManager
    {
        $locRepo = m::mock();
        $locRepo->expects('maybeRemoveAdrColumn')->andReturn(['foo' => 'bar']);

        $serviceManager = m::mock(RepositoryServiceManager::class);
        $serviceManager->expects('get')->with('LicenceOperatingCentre')->andReturn($locRepo);

        return $serviceManager;
    }
}
