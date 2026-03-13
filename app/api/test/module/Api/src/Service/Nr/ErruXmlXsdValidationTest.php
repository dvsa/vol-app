<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nr;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as ErruRequestEntity;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty as SiPenaltyEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Service\Nr\CheckGoodRepute;
use Dvsa\Olcs\Api\Service\Nr\MsiResponse;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use Olcs\XmlTools\Xml\XmlNodeBuilder;

class ErruXmlXsdValidationTest extends MockeryTestCase
{
    private const XML_NS = 'https://webgate.ec.testa.eu/move-hub/erru/3.5';
    private const ERRU_VERSION = '3.5';
    private const XSD_DIR = __DIR__ . '/../../../../../../module/Api/data/nr/xsd/3.5/';

    public function testMsiResponseXmlValidatesAgainstXsd(): void
    {
        $xmlBuilder = new XmlNodeBuilder('NotifyCheckResult_Response', self::XML_NS, []);
        $sut = new MsiResponse($xmlBuilder, self::ERRU_VERSION);

        $cases = $this->buildMsiMocks();
        $xmlString = $sut->create($cases);

        $this->assertXmlValidatesAgainstXsd($xmlString, self::XSD_DIR . 'NotifyCheckResult_Response.xsd');
    }

    public function testCheckGoodReputeXmlValidatesAgainstXsd(): void
    {
        Logger::setLogger(m::mock(\Laminas\Log\Logger::class)->shouldIgnoreMissing());

        $xmlBuilder = new XmlNodeBuilder('CheckGoodRepute_Request', self::XML_NS, []);
        $sut = new CheckGoodRepute($xmlBuilder, self::ERRU_VERSION);

        $transportManager = $this->buildCgrMocks();
        $xmlString = $sut->create($transportManager);

        $this->assertXmlValidatesAgainstXsd($xmlString, self::XSD_DIR . 'CheckGoodRepute_Request.xsd');
    }

    private function buildMsiMocks(): CasesEntity
    {
        $penalty1 = m::mock(SiPenaltyEntity::class)->makePartial();
        $penalty1->expects('getSiPenaltyType->getId')->andReturn(101);
        $penalty1->expects('getStartDate')->andReturnNull();
        $penalty1->expects('getEndDate')->andReturnNull();
        $penalty1->expects('getImposed')->andReturn('N');
        $penalty1->expects('getReasonNotImposed')->andReturn('Further sanction not required');
        $penalty1->expects('getSiPenaltyErruRequested->getPenaltyRequestedIdentifier')->andReturn(1);

        $penalty2 = m::mock(SiPenaltyEntity::class);
        $penalty2->expects('getSiPenaltyType->getId')->andReturn(301);
        $penalty2->expects('getStartDate')->andReturn('2024-02-01');
        $penalty2->expects('getEndDate')->andReturn('2024-08-01');
        $penalty2->expects('getImposed')->andReturn('Y');
        $penalty2->expects('getSiPenaltyErruRequested->getPenaltyRequestedIdentifier')->andReturn(2);

        $appliedPenalties = new PersistentCollection(
            m::mock(EntityManagerInterface::class),
            SiPenaltyEntity::class,
            new ArrayCollection([$penalty1, $penalty2])
        );

        $si = m::mock(SiEntity::class);
        $si->expects('getAppliedPenalties')->andReturn($appliedPenalties);

        $contactDetails = m::mock(ContactDetails::class);
        $contactDetails->expects('getAddress->toArray')->andReturn([
            'addressLine1' => '123 Test Street',
            'postcode' => 'LS1 1AA',
            'town' => 'Leeds',
        ]);

        $licence = m::mock(Licence::class);
        $licence->expects('getContactAddress')->andReturn($contactDetails);

        $erruRequest = m::mock(ErruRequestEntity::class);
        $erruRequest->expects('getWorkflowId')->andReturn('e933f62c-ceae-4833-b022-c4f69e2211ef');
        $erruRequest->expects('getNotificationNumber')->andReturn('0ffefb6b-6344-4a60-9a53-4381c32f');
        $erruRequest->expects('getOriginatingAuthority')->andReturn('Driver and Vehicle Agency');
        $erruRequest->expects('getTransportUndertakingName')->andReturn('Test Transport Ltd');
        $erruRequest->expects('getCommunityLicenceNumber')->andReturn('UKGB/OB1234567/00001');
        $erruRequest->expects('getTotAuthVehicles')->andReturn(10);
        $erruRequest->expects('getCommunityLicenceStatus->getDescription')->andReturn('Active');

        $cases = m::mock(CasesEntity::class);
        $cases->expects('canSendMsiResponse')->andReturnTrue();
        $cases->expects('getLicence')->andReturn($licence);
        $cases->expects('getErruRequest')->andReturn($erruRequest);
        $cases->expects('getSeriousInfringements')->andReturn(new ArrayCollection([$si]));

        return $cases;
    }

    private function buildCgrMocks(): TransportManager
    {
        $person = m::mock(\Dvsa\Olcs\Api\Entity\Person\Person::class);
        $person->expects('getFamilyName')->andReturn('Smith');
        $person->expects('getForename')->andReturn('John');
        $person->expects('getBirthDate')->andReturn('1980-05-15');
        $person->expects('getBirthPlace')->andReturn('London');

        $qualification = m::mock(\Dvsa\Olcs\Api\Entity\Tm\TmQualification::class);
        $qualification->expects('getCountryCode->getId')->andReturn('GB');
        $qualification->expects('getSerialNo')->andReturn('CPC001');
        $qualification->expects('getIssuedDate')->andReturn('2015-06-01');

        $qualifications = new ArrayCollection([$qualification]);

        $homeCd = m::mock(\Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails::class);
        $homeCd->expects('getPerson')->andReturn($person);

        $tm = m::mock(TransportManager::class);
        $tm->expects('hasReputeCheckAddress')->andReturnTrue();
        $tm->expects('getMostRecentQualification')->andReturn($qualifications);
        $tm->expects('getHomeCd')->andReturn($homeCd);

        return $tm;
    }

    private function assertXmlValidatesAgainstXsd(string $xml, string $xsdPath): void
    {
        $this->assertFileExists($xsdPath, "XSD file not found: $xsdPath");

        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        libxml_use_internal_errors(true);
        $isValid = $dom->schemaValidate($xsdPath);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = sprintf('Line %d: %s', $error->line, trim($error->message));
        }

        $this->assertTrue(
            $isValid,
            "XML failed XSD validation:\n" . implode("\n", $errorMessages) . "\n\nXML:\n" . $xml
        );
    }
}
