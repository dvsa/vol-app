<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Entity;

use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\OlcsTest\Integration\IntegrationTestCase;
use Dvsa\OlcsTest\Support\EntityFieldReads;
use PHPUnit\Framework\Attributes\DataProvider;

class FilteredFieldReadTest extends IntegrationTestCase
{
    private const ENTITY_NAMESPACE = 'Dvsa\\Olcs\\Api\\Entity\\';

    public static function filteredFields(): array
    {
        $fields = [
            'Application\\Application' => ['isVariation'],
            'Application\\ApplicationOperatingCentre' => ['operatingCentre', 'action', 'id', 'isInterim'],
            'Generic\\ApplicationPath' => ['effectiveFrom'],
            'Bus\\BusReg' => ['regNo', 'variationNo', 'licence', 'routeNo'],
            'CommunityLic\\CommunityLic' => ['specifiedDate', 'status'],
            'CommunityLic\\CommunityLicSuspension' => ['startDate'],
            'CommunityLic\\CommunityLicWithdrawal' => ['startDate'],
            'Cases\\Complaint' => ['isCompliance'],
            'Cases\\ConditionUndertaking' => ['application', 'licence', 'isDraft', 'isFulfilled', 'attachedTo', 'conditionType'],
            'ContactDetails\\Country' => ['countryDesc'],
            'Doc\\Document' => ['operatingCentre', 'category', 'subCategory', 'isPostSubmissionUpload'],
            'Fee\\Fee' => ['feeStatus', 'invoicedDate'],
            'Permits\\IrhpCandidatePermit' => ['successful', 'wanted'],
            'Permits\\IrhpPermitRange' => ['ssReserve', 'lostReplacement', 'fromNo', 'journey'],
            'Licence\\Licence' => ['inForceDate', 'licNo'],
            'Licence\\LicenceOperatingCentre' => ['operatingCentre'],
            'Licence\\LicenceVehicle' => ['removalDate', 'id', 'vehicle', 'specifiedDate', 'interimApplication', 'application'],
            'Organisation\\OrganisationPerson' => ['id'],
            'Organisation\\OrganisationUser' => ['isAdministrator'],
            'OtherLicence\\OtherLicence' => ['previousLicenceType'],
            'Licence\\PsvDisc' => ['ceasedDate'],
            'Publication\\PublicationLink' => ['publicationSection'],
            'Generic\\QuestionText' => ['effectiveFrom'],
            'Application\\S4' => ['agreedDate', 'outcome'],
            'Cases\\Stay' => ['stayType'],
            'Task\\Task' => ['category', 'subCategory', 'isClosed', 'description'],
            'Task\\TaskAlphaSplit' => ['letters'],
            'PrintScan\\TeamPrinter' => ['user', 'subCategory'],
            'Tm\\TmQualification' => ['issuedDate', 'id'],
            'Organisation\\TradingName' => ['licence', 'deletedDate', 'createdOn'],
            'Tm\\TransportManagerApplication' => ['action'],
            'Tm\\TransportManagerLicence' => ['transportManager'],
        ];

        $dataSets = [];
        foreach ($fields as $class => $classFields) {
            $dataSets[$class] = [self::ENTITY_NAMESPACE . $class, $classFields];
        }

        return $dataSets;
    }

    #[DataProvider('filteredFields')]
    public function testGetterAndStoredValueAgreeOnLoadedRows(string $class, array $fields): void
    {
        $rows = $this->em()->createQuery("SELECT e FROM $class e")->setMaxResults(50)->getResult();

        if ($rows === []) {
            self::markTestSkipped("no $class rows in the local database");
        }

        foreach ($rows as $row) {
            foreach ($fields as $field) {
                $this->assertSame(
                    EntityFieldReads::viaGetter($row, $field),
                    EntityFieldReads::stored($row, $field),
                    "$class::\$$field"
                );
            }
        }
    }

    public function testStoredReadLoadsALazyRecord(): void
    {
        $this->em()->clear();
        $licenceVehicle = $this->em()->createQuery('SELECT lv FROM ' . LicenceVehicle::class . ' lv')
            ->setMaxResults(1)->getOneOrNullResult();

        if ($licenceVehicle === null) {
            self::markTestSkipped('no LicenceVehicle rows in the local database');
        }

        $unitOfWork = $this->em()->getUnitOfWork();
        $vehicle = EntityFieldReads::stored($licenceVehicle, 'vehicle');

        $this->assertTrue($unitOfWork->isUninitializedObject($vehicle));
        $vrm = EntityFieldReads::stored($vehicle, 'vrm');
        $this->assertFalse($unitOfWork->isUninitializedObject($vehicle));
        $this->assertSame($vehicle->getVrm(), $vrm);
    }
}
