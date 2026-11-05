<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\Cases\ByLicence;
use Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact\GetList as PhoneContactList;
use Dvsa\Olcs\Transfer\Query\Fee\FeeTypeList;
use Dvsa\OlcsTest\Integration\IntegrationTestCase;

/**
 * Queries where the "with" helper is asked for a path it has already joined. It renames or
 * skips the join, so these run the real queries to make sure every alias they use is still
 * there. The unit tests only compare DQL strings and can't see a missing alias.
 */
class QueryPartialJoinsTest extends IntegrationTestCase
{
    public function testLicenceWithAddresses(): void
    {
        $id = $this->em()->createQuery('SELECT MIN(l.id) FROM ' . Licence::class . ' l')->getSingleScalarResult();

        $this->assertInstanceOf(Licence::class, $this->repo('Licence')->fetchWithAddressesUsingId((int) $id));
    }

    public function testPhoneContactList(): void
    {
        $id = $this->em()->createQuery('SELECT MIN(IDENTITY(pc.contactDetails)) FROM ' . PhoneContact::class . ' pc')
            ->getSingleScalarResult();

        $result = $this->repo('PhoneContact')->fetchList(
            PhoneContactList::create(['contactDetailsId' => (int) $id]),
            Query::HYDRATE_OBJECT
        );

        $this->assertNotEmpty(iterator_to_array($result));
    }

    public function testCasesList(): void
    {
        $id = $this->em()->createQuery('SELECT MIN(IDENTITY(c.licence)) FROM ' . Cases::class . ' c')
            ->getSingleScalarResult();

        $result = $this->repo('Cases')->fetchList(
            ByLicence::create(['licence' => (int) $id, 'page' => 1, 'limit' => 10]),
            Query::HYDRATE_OBJECT
        );

        $this->assertNotEmpty(iterator_to_array($result));
    }

    public function testFeeTypeListOrderedByFeeType(): void
    {
        $result = $this->repo('FeeType')->fetchList(FeeTypeList::create(['isMiscellaneous' => 'Y']), Query::HYDRATE_OBJECT);

        $this->assertNotEmpty(iterator_to_array($result));
    }

    public function testContinuationDetails(): void
    {
        $id = $this->em()->createQuery('SELECT MIN(IDENTITY(cd.continuation)) FROM ' . ContinuationDetail::class . ' cd')
            ->getSingleScalarResult();

        $result = $this->repo('ContinuationDetail')->fetchDetails((int) $id, null, null, null, null);

        $this->assertNotEmpty($result);
    }
}
