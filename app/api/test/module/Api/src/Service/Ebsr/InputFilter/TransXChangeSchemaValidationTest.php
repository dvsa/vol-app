<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\InputFilter;

use DOMDocument;
use DOMXPath;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\XmlStructureInputFactory;
use Olcs\XmlTools\Validator\Xsd;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Validates registration documents against the real TransXChange schemas, set up from the real config (ebsr, xsd
 * mappings, message exclusions) the way XmlStructureInputFactory does it.
 *
 * TransXChange 2.5.2 is a superset of 2.5 except that it drops five authority names outright (ComhairleNanEileanSiar,
 * IsleOfAnglesey, NorthLincolnshire, RhonddaCynonTaff, ValeOfGlamorgan). Those are still the names in
 * local_authority.txc_name, so documents using them must stay valid through the 2.5 fallback schema.
 */
final class TransXChangeSchemaValidationTest extends TestCase
{
    private const string XSD_DIR = '/module/Api/data/ebsr/xsd/';

    private const string REGISTRATION = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<TransXChange xmlns="http://www.transxchange.org.uk/"
    xmlns:apd="http://www.govtalk.gov.uk/people/AddressAndPersonalDetails"
    CreationDateTime="2026-09-29T09:00:00" ModificationDateTime="2026-09-29T09:00:00" Modification="new"
    RevisionNumber="0" FileName="registration.xml" SchemaVersion="2.5" RegistrationDocument="true">
    <Operators>
        <LicensedOperator id="O1">
            <OperatorCode>ABC</OperatorCode>
            <OperatorShortName>ABC Buses</OperatorShortName>
            <OperatorNameOnLicence>ABC Buses Ltd</OperatorNameOnLicence>
            <LicenceNumber>PB1234567</LicenceNumber>
            <LicenceClassification>standardNational</LicenceClassification>
            <EnquiryTelephoneNumber>
                <TelNationalNumber>0207123456</TelNationalNumber>
            </EnquiryTelephoneNumber>
            <ContactTelephoneNumber>
                <TelNationalNumber>0207654321</TelNationalNumber>
            </ContactTelephoneNumber>
            <OperatorAddresses>
                <CorrespondenceAddress>
                    <apd:Line>45 City Road</apd:Line>
                    <apd:Line>London</apd:Line>
                    <apd:PostCode>EC1V 3PH</apd:PostCode>
                </CorrespondenceAddress>
            </OperatorAddresses>
        </LicensedOperator>
    </Operators>
    <Services>
        <Service>
            <ServiceCode>PB1234567:1</ServiceCode>
            <Lines>
                <Line id="l_1">
                    <LineName>1</LineName>
                </Line>
            </Lines>
            <OperatingPeriod>
                <StartDate>2026-10-01</StartDate>
            </OperatingPeriod>
            <RegisteredOperatorRef>O1</RegisteredOperatorRef>
            <StandardService>
                <Origin>Suborn</Origin>
                <Destination>Egham</Destination>
                <Cancellation/>
            </StandardService>
        </Service>
    </Services>
    <Registrations>
        <Registration>
            <ServiceRef>PB1234567:1</ServiceRef>
            <SubmissionDate>2026-09-29</SubmissionDate>
            <VosaRegistrationNumber>
                <TanCode>PB</TanCode>
                <LicenceNumber>1234567</LicenceNumber>
                <RegistrationNumber>1</RegistrationNumber>
            </VosaRegistrationNumber>
            <ApplicationClassification>cancel</ApplicationClassification>
            <VariationNumber>2</VariationNumber>
            <SubmissionAuthor>
                <Position>Operations Director</Position>
                <Title>Mr</Title>
                <Forename>Adam</Forename>
                <Surname>Smith</Surname>
            </SubmissionAuthor>
            <TrafficAreas>
                <TrafficArea>
                    <TrafficAreaName>Western</TrafficAreaName>
                </TrafficArea>
            </TrafficAreas>
            <CirculatedAuthorities>%s</CirculatedAuthorities>
            <SubsidyDetails>
                <NoSubsidy/>
            </SubsidyDetails>
        </Registration>
    </Registrations>
</TransXChange>
XML;

    /**
     * Every authority name in either schema's list is accepted: the 2.5.2 names by the main schema, and the 2.5 names
     * (including the five 2.5.2 dropped) by the fallback
     */
    #[DataProvider('provideSchemaVersionKey')]
    public function testEveryAuthorityNameInSchemaIsValid(string $versionKey): void
    {
        $authorityNames = $this->authorityNames(self::ebsrConfig()[$versionKey]);

        $sut = $this->createSut();

        $this->assertTrue(
            $sut->isValid($this->registration(...$authorityNames)),
            implode("\n", $sut->getMessages())
        );
    }

    public static function provideSchemaVersionKey(): array
    {
        return [
            'main schema' => ['transxchange_schema_version'],
            'fallback schema' => ['transxchange_fallback_schema_version'],
        ];
    }

    #[DataProvider('provideAuthorityName')]
    public function testAuthorityNameIsValid(string $authorityName): void
    {
        $sut = $this->createSut();

        $this->assertTrue($sut->isValid($this->registration($authorityName)), implode("\n", $sut->getMessages()));
    }

    public static function provideAuthorityName(): array
    {
        return [
            'in both schemas' => ['Bristol'],
            'new in 2.5.2' => ['GreaterManchesterMCA'],
            '2.5.2 spelling' => ['IsleofAnglesey'],
            'dropped by 2.5.2, respelt as ComhairlenanEileanSiar' => ['ComhairleNanEileanSiar'],
            'dropped by 2.5.2, respelt as IsleofAnglesey' => ['IsleOfAnglesey'],
            'dropped by 2.5.2, respelt as RhonddaCynonTaf' => ['RhonddaCynonTaff'],
            'dropped by 2.5.2, respelt as ValeofGlamorgan' => ['ValeOfGlamorgan'],
            'dropped by 2.5.2, no replacement' => ['NorthLincolnshire'],
        ];
    }

    public function testUnknownAuthorityNameIsReportedAgainstMainSchema(): void
    {
        $sut = $this->createSut();

        $this->assertFalse($sut->isValid($this->registration('NotAnAuthority')));

        $messages = implode("\n", $sut->getMessages());
        $this->assertStringContainsString("The value 'NotAnAuthority' is not an element of the set", $messages);
        // only the 2.5.2 list has this name, so the reported errors are the main schema's
        $this->assertStringContainsString("'GreaterManchesterMCA'", $messages);
    }

    /**
     * Known limitation of the fallback: a document is checked against one schema at a time, so one that needs both
     * a name 2.5.2 dropped and a name 2.5.2 added is rejected
     */
    public function testMixingDroppedAndNewAuthorityNamesIsInvalid(): void
    {
        $sut = $this->createSut();

        $this->assertFalse($sut->isValid($this->registration('NorthLincolnshire', 'GreaterLincolnshireMCCA')));
    }

    private function createSut(): Xsd
    {
        $ebsrConfig = self::ebsrConfig();
        $moduleConfig = require self::apiDir() . '/module/Api/config/module.config.php';
        $globalConfig = require self::apiDir() . '/config/autoload/config.global.php';

        $sut = new Xsd();
        $sut->setMappings($moduleConfig['xsd_mappings']);
        $sut->setMaxErrors($ebsrConfig['max_schema_errors']);
        $sut->setXmlMessageExclude($globalConfig['xml_valid_message_exclude']);
        $sut->setXsd(sprintf(XmlStructureInputFactory::XSD_PATH, $ebsrConfig['transxchange_schema_version']));
        $sut->setFallbackXsd(
            sprintf(XmlStructureInputFactory::XSD_PATH, $ebsrConfig['transxchange_fallback_schema_version'])
        );

        return $sut;
    }

    private function registration(string ...$authorityNames): DOMDocument
    {
        $circulatedAuthorities = '';

        foreach ($authorityNames as $authorityName) {
            $circulatedAuthorities .= sprintf(
                '<CirculatedAuthority><AuthorityName>%s</AuthorityName></CirculatedAuthority>',
                $authorityName
            );
        }

        $document = new DOMDocument();
        $document->loadXML(sprintf(self::REGISTRATION, $circulatedAuthorities));

        return $document;
    }

    /**
     * The authority names listed in the given schema version, read from the schema itself
     *
     * @return string[]
     */
    private function authorityNames(string $schemaVersion): array
    {
        $authoritiesXsd = glob(
            self::apiDir() . self::XSD_DIR . 'TransXChange_schema_' . $schemaVersion . '/txc/TXC_authorities-*.xsd'
        );
        $this->assertCount(1, $authoritiesXsd);

        $schema = new DOMDocument();
        $schema->load($authoritiesXsd[0]);

        $xpath = new DOMXPath($schema);
        $xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema');

        $authorityNames = [];

        foreach ($xpath->query("//xsd:simpleType[@name='AuthorityNameEnumeration']//xsd:enumeration/@value") as $value) {
            $authorityNames[] = $value->nodeValue;
        }

        $this->assertNotEmpty($authorityNames);

        return $authorityNames;
    }

    private static function ebsrConfig(): array
    {
        return (require self::apiDir() . '/config/autoload/global.php')['ebsr'];
    }

    private static function apiDir(): string
    {
        return dirname(__DIR__, 7);
    }
}
