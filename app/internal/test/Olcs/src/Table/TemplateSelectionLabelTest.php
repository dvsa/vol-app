<?php

declare(strict_types=1);

namespace OlcsTest\Table;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TemplateSelectionLabelTest extends TestCase
{
    #[DataProvider('rows')]
    public function testConfiguredLabel(string $module, string $table, array $row, string $expected): void
    {
        $config = require __DIR__ . '/../../../../module/' . $module . '/src/Table/Tables/' . $table . '.table.php';
        $columns = array_filter(
            $config['columns'],
            static fn($column) => isset($column['format'], $column['label-callback'])
                && in_array($column['format'], ['{{[elements/radio]}}', '{{[elements/checkbox]}}'], true)
        );
        self::assertCount(1, $columns);
        $column = reset($columns);

        self::assertContains($column['format'], ['{{[elements/radio]}}', '{{[elements/checkbox]}}']);
        self::assertSame($expected, $column['label-callback']($row));
    }

    public static function rows(): array
    {
        return [
            ['Admin', 'admin-teams', ['name' => 'Team A'], 'Select team Team A'],
            ['Admin', 'admin-local-authority', ['description' => 'York'], 'Select local authority York'],
            ['Admin', 'recipient', ['contactName' => 'Sam', 'emailAddress' => 'sam@example.test'], 'Select recipient Sam, sam@example.test'],
            ['Admin', 'partner', ['description' => 'Partner A'], 'Select partner organisation Partner A'],
            ['Admin', 'admin-feature-toggles', ['friendlyName' => 'Example'], 'Select feature toggle Example'],
            ['Admin', 'admin-system-parameters', ['id' => 'example'], 'Select parameter example'],
            ['Admin', 'admin-email-template-groups', ['description' => 'Subject'], 'Select email template Subject'],
            ['Admin', 'admin-sms-templates', ['description' => 'Reminder'], 'Select SMS template Reminder'],
            ['Admin', 'admin-editable-translations', ['translationKey' => 'key'], 'Select translation key'],
            ['Admin', 'translation-key-texts', ['language' => ['name' => 'Welsh']], 'Select translation for Welsh'],
            ['Admin', 'admin-letter-todo', ['id' => 7, 'name' => 'Review'], 'Select letter todo Review, reference 7'],
            ['Admin', 'admin-letter-issue-type', ['id' => 7, 'name' => 'Issue'], 'Select letter issue type Issue, reference 7'],
            ['Admin', 'admin-letter-issue', ['id' => 7, 'issueKey' => 'KEY'], 'Select letter issue KEY, reference 7'],
            ['Admin', 'admin-letter-type', ['id' => 7, 'name' => 'Type'], 'Select letter type Type, reference 7'],
            ['Admin', 'admin-letter-section', ['id' => 7, 'name' => 'Section'], 'Select letter section Section, reference 7'],
            ['Admin', 'admin-letter-test-data', ['id' => 7, 'name' => 'Data'], 'Select test data Data, reference 7'],
            ['Admin', 'admin-letter-choice', ['id' => 7, 'label' => 'Choice'], 'Select letter choice Choice, reference 7'],
            ['Admin', 'admin-letter-appendix', ['id' => 7, 'name' => 'Appendix'], 'Select letter appendix Appendix, reference 7'],
            ['Admin', 'admin-letter-choice', ['id' => 7], 'Select letter choice , reference 7'],
            ['Admin', 'admin-master-template', ['name' => 'Letter', 'locale' => 'en_GB'], 'Select master template Letter, en_GB'],
            ['Olcs', 'irhp-permits', ['permitNumber' => 'P123'], 'Select permit P123'],
            ['Olcs', 'irhp-permits-ecmt', ['permitNumber' => 'P123'], 'Select permit P123'],
            ['Olcs', 'unlicensed-vehicles', ['vehicle' => ['vrm' => 'AB12 CDE']], 'Select vehicle AB12 CDE'],
            ['Admin', 'admin-fee-rates', ['id' => 7, 'description' => 'Application'], 'Select fee rate 7, Application'],
            ['Admin', 'admin-document-templates', ['description' => 'Letter', 'filename' => 'letter.doc'], 'Select document template Letter, letter.doc'],
            ['Admin', 'admin-replacements', ['id' => 7, 'placeholder' => 'name'], 'Select replacement name, 7'],
            ['Admin', 'admin-presiding-tcs', ['name' => 'Example'], 'Select presiding TC Example'],
            ['Admin', 'admin-publication', ['publicationNo' => '123'], 'Select publication 123'],
            ['Admin', 'admin-irhp-permit-range', ['prefix' => 'GB', 'fromNo' => 1, 'toNo' => 10], 'Select permit range GB1 to GB10'],
            ['Olcs', 'cases', ['id' => 7], 'Select case 7'],
            ['Olcs', 'fees', ['id' => 7], 'Select fee 7'],
            ['Olcs', 'inspectionRequest', ['id' => 7], 'Select inspection request 7'],
            ['Olcs', 'condition', ['id' => 7], 'Select condition or undertaking 7'],
            ['Olcs', 'serious-infringement', ['id' => 7], 'Select serious infringement 7'],
            ['Olcs', 'operator-users', ['loginId' => 'sam'], 'Select user sam'],
            ['Olcs', 'erru-applied', ['id' => 7], 'Select penalty 7'],
            ['Olcs', 'busreg', ['regNo' => 'PB123', 'variationNo' => 2], 'Select bus registration PB123, variation 2'],
            ['Olcs', 'complaint', ['id' => 7, 'complaintDate' => '2025-01-01', 'complainantContactDetails' => ['person' => ['forename' => 'Sam', 'familyName' => 'Jones']]], 'Select complaint from Sam Jones, 2025-01-01, reference 7'],
            ['Olcs', 'prohibition', ['id' => 7, 'vrm' => 'AB12 CDE', 'prohibitionDate' => '2025-01-01'], 'Select prohibition AB12 CDE, 2025-01-01, reference 7'],
            ['Olcs', 'statement', ['id' => 7, 'statementType' => ['description' => 'Witness'], 'requestedDate' => '2025-01-01'], 'Select statement Witness, 2025-01-01, reference 7'],
            ['Olcs', 'conviction', ['id' => 7, 'personFirstname' => 'Sam', 'personLastname' => 'Jones'], 'Select conviction N/A, Sam Jones, reference 7'],
            ['Olcs', 'hearings', ['id' => 7, 'hearingDate' => '2025-01-01'], 'Select hearing 2025-01-01, reference 7'],
            ['Olcs', 'piHearing', ['id' => 7, 'hearingDate' => '2025-01-01'], 'Select hearing 2025-01-01, reference 7'],
            ['Olcs', 'NonPi', ['id' => 7, 'hearingDate' => '2025-01-01'], 'Select meeting 2025-01-01, reference 7'],
            ['Olcs', 'opposition', ['id' => 7, 'raisedDate' => '2025-01-01', 'opposer' => ['contactDetails' => ['person' => ['forename' => 'Sam', 'familyName' => 'Jones']]]], 'Select opposition from Sam Jones, 2025-01-01, reference 7'],
            ['Admin', 'admin-irhp-permit-stock', ['id' => 7, 'country' => ['countryDesc' => 'France'], 'validFrom' => '2025-01-01'], 'Select permit stock France, 2025-01-01, reference 7'],
            ['Admin', 'admin-irhp-permit-window', ['id' => 7, 'startDate' => '2025-01-01', 'endDate' => '2025-02-01'], 'Select permit window 2025-01-01 to 2025-02-01, reference 7'],
            ['Admin', 'admin-printers-exceptions', ['id' => 7, 'printerName' => 'Office'], 'Select printer exception Office, reference 7'],
            ['Admin', 'task-alpha-split', ['id' => 7, 'letters' => 'A-C'], 'Select alpha split A-C, reference 7'],
            ['Olcs', 'prohibitionDefect', ['id' => 7, 'defectType' => 'Brakes'], 'Select defect Brakes, reference 7'],
            ['Olcs', 'publication', ['id' => 7, 'publication' => ['publicationNo' => '123']], 'Select publication 123, reference 7'],
            ['Olcs', 'tm.publication', ['id' => 7, 'publication' => ['publicationNo' => '123']], 'Select publication 123, reference 7'],
            ['Olcs', 'environmental-complaints', ['id' => 7, 'complaintDate' => '2025-01-01', 'complainantContactDetails' => ['person' => ['forename' => 'Sam', 'familyName' => 'Jones']]], 'Select environmental complaint from Sam Jones, 2025-01-01, reference 7'],
            ['Olcs', 'note', ['id' => 7, 'comment' => 'Review'], 'Select note Review, reference 7'],
            ['Olcs', 'impounding', ['id' => 7, 'applicationReceiptDate' => '2025-01-01'], 'Select impounding application 2025-01-01, reference 7'],
            ['Olcs', 'operator.irfo.psv-authorisations', ['id' => 7], 'Select PSV authorisation 7'],
            ['Olcs', 'irhp-permits-pre-grant', ['id' => 7, 'assignedEmissionsCategory' => ['description' => 'Euro VI']], 'Select pending permit Euro VI, reference 7'],
            ['Olcs', 'SubmissionSections/operating-centres', ['OcAddress' => ['addressLine1' => '1 High Street', 'town' => 'Leeds', 'postcode' => 'LS1']], 'Select operating centre 1 High Street, Leeds, LS1'],
            ['Olcs', 'SubmissionSections/people', ['forename' => 'Sam', 'familyName' => 'Jones'], 'Select person Sam Jones'],
            ['Olcs', 'SubmissionSections/applications', ['applicationId' => 42, 'licNo' => 'OB123'], 'Select application 42, licence OB123'],
            ['Olcs', 'SubmissionSections/auth-requested-applied-for', ['id' => 42], 'Select application 42'],
            ['Olcs', 'SubmissionSections/applied-penalties', ['id' => 42], 'Select penalty 42'],
            ['Olcs', 'SubmissionSections/conditions', ['id' => 42], 'Select condition 42'],
            ['Olcs', 'SubmissionSections/undertakings', ['id' => 42], 'Select undertaking 42'],
            ['Olcs', 'SubmissionSections/linked-licences-app-numbers', ['licNo' => 'OB123'], 'Select licence OB123'],
            ['Olcs', 'SubmissionSections/licences', ['licNo' => 'OB123', 'organisationName' => 'Example'], 'Select licence OB123, Example'],
            ['Olcs', 'SubmissionSections/revoked-curtailed-suspended-licences', ['licNo' => 'OB123', 'holderName' => 'Example'], 'Select licence OB123, Example'],
            ['Olcs', 'SubmissionSections/tm-details', ['forename' => 'Sam', 'familyName' => 'Jones'], 'Select transport manager Sam Jones'],
            ['Olcs', 'SubmissionSections/transport-managers', ['forename' => 'Sam', 'familyName' => 'Jones'], 'Select transport manager Sam Jones'],
            ['Olcs', 'SubmissionSections/tm-other-employment', ['employerName' => 'Example', 'position' => 'Manager'], 'Select employment with Example, Manager'],
            ['Olcs', 'SubmissionSections/tm-qualifications', ['qualificationType' => 'CPC', 'serialNo' => '123'], 'Select qualification CPC, 123'],
            ['Olcs', 'SubmissionSections/outstanding-applications', ['id' => 42, 'licNo' => 'OB123'], 'Select application 42, licence OB123'],
            ['Olcs', 'SubmissionSections/oppositions', ['contactName' => ['forename' => 'Sam', 'familyName' => 'Jones'], 'oppositionType' => 'Objection'], 'Select opposition from Sam Jones, Objection'],
            ['Olcs', 'SubmissionSections/compliance-complaints', ['complainantForename' => 'Sam', 'complainantFamilyName' => 'Jones', 'complaintDate' => '2025-01-01'], 'Select compliance complaint from Sam Jones, 2025-01-01'],
            ['Olcs', 'SubmissionSections/environmental-complaints', ['complainantForename' => 'Sam', 'complainantFamilyName' => 'Jones', 'complaintDate' => '2025-01-01'], 'Select environmental complaint from Sam Jones, 2025-01-01'],
            ['Olcs', 'SubmissionSections/conviction-fpn-offence-history', ['name' => 'Sam', 'categoryText' => 'Offence'], 'Select offence for Sam, Offence'],
            ['Olcs', 'SubmissionSections/convictions-and-penalties', ['offence' => 'Speeding', 'courtFpn' => 'Court'], 'Select conviction Speeding, Court'],
            ['Olcs', 'SubmissionSections/prohibition-history', ['prohibitionDate' => '2025-01-01', 'vehicle' => 'AB12 CDE'], 'Select prohibition 2025-01-01, AB12 CDE'],
            ['Olcs', 'SubmissionSections/imposed-penalties', ['penaltyType' => 'Fine', 'startDate' => '2025-01-01'], 'Select imposed penalty Fine, 2025-01-01'],
            ['Olcs', 'SubmissionSections/requested-penalties', ['penaltyType' => 'Fine', 'duration' => '1 day'], 'Select requested penalty Fine, 1 day'],
            ['Olcs', 'SubmissionSections/statements', ['statementType' => 'Witness', 'requestedDate' => '2025-01-01'], 'Select statement Witness, 2025-01-01'],
        ];
    }
}
