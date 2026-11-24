<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework;

use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Generic\Question;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingContent;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingUserMessageRead;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * View variables for ViewRenderingTest, shaped like the ones production passes.
 *
 * Values that reach a template carry `<&>` where escaping matters, so a snapshot shows which variables a
 * template escapes and which it prints raw.
 */
final class ViewRenderingFixtures
{
    /**
     * Applied by the translator to every translated string, as the replacement table from the database is.
     */
    public const string REPLACEMENT_TOKEN = '{{FRAMEWORK_TEST_REPLACEMENT}}';

    private const string STRING = Question::QUESTION_TYPE_STRING;

    /**
     * The translations the translator loads, in the cache's nested shape: text domain => locale => key => text.
     * Only a handful of keys are translated: enough to show translation (and the replacement table) is applied.
     * Every other key renders as itself, and every `markup-*` key renders its partial.
     *
     * @return array<string, array<string, array<string, string>>>
     */
    public static function translations(string $locale): array
    {
        $messages = [
            'en_GB' => [
                'review-page-title' => 'Review your application ' . self::REPLACEMENT_TOKEN,
                'header-vehicle-operator-licensing' => 'Vehicle Operator Licensing',
                'header-gov.uk' => 'GOV.UK',
                'Licence review' => 'Licence review',
                'permits.snapshot.title' => '%s permit application',
                'permits.check-your-answers.no-of-permits.year' => 'Permits for %d',
                'permits.check-your-answers.no-of-permits' => '%d permits for %d',
                'permits.check-your-answers.bilateral.standard' => '%d standard permits',
                'qanda.common.no-of-permits.emissions-category.line' => '%d permits for %s vehicles',
                'Yes' => 'Yes',
                'No' => 'No',
                'Messages' => 'messages',
                'email.licensing-information.conversation.body' => 'You have a new message <&>',
            ],
            'cy_GB' => [
                'header-gov.uk' => 'GOV.UK (cy)',
                'Yes' => 'Ie',
                'No' => 'Na',
            ],
        ];

        return ['default' => [$locale => $messages[$locale] ?? ['header-gov.uk' => 'GOV.UK (' . $locale . ')']]];
    }

    /**
     * The read-only documents the Snapshot generators render through AbstractGenerator::generateReadonly(),
     * one per layout (two for the permit layout, which takes either question partial). Built lazily: the
     * conversation layout changes its entities' dates while it renders.
     *
     * @return array<string, array{string, \Closure(): array<string, mixed>}> case => [layout, variables]
     */
    public static function snapshotDocuments(): array
    {
        return [
            'review' => ['review', self::review(...)],
            'continuation-review' => ['continuation-review', self::continuationReview(...)],
            'permit-application' => ['permit-application', static fn(): array => self::permitApplication(false)],
            'permit-application-qa' => ['permit-application', static fn(): array => self::permitApplication(true)],
            'conversation' => ['conversation', self::conversation(...)],
            'check-repute-response' => ['check-repute-response', self::checkReputeResponse(...)],
        ];
    }

    /**
     * The permit answers-summary templates, rendered by name as the AnswersSummary row adders and the QA row
     * generator do (`answers-summary/<template>`), covering each branch.
     *
     * @return array<string, array{string, array<string, mixed>}> case => [template name, variables]
     */
    public static function answersSummaries(): array
    {
        return [
            'generic' => ['answers-summary/generic', ['answer' => 'Annual ECMT <&>']],
            'licence' => [
                'answers-summary/licence',
                ['licenceNo' => 'OG1234567 <&>', 'trafficAreaName' => 'North East of England'],
            ],
            'bilateral-country-names' => [
                'answers-summary/bilateral-country-names',
                ['countryNames' => ['Norway', 'Morocco <&>']],
            ],
            'bilateral-permits-required' => [
                'answers-summary/bilateral-permits-required',
                ['rows' => [
                    ['key' => 'permits.check-your-answers.bilateral.standard', 'count' => 3],
                    ['key' => 'permits.untranslated.key <&>', 'count' => 5],
                ]],
            ],
            'bilateral-standard-and-cabotage' => [
                'answers-summary/bilateral-standard-and-cabotage',
                ['yesNo' => 'Yes', 'additionalInfo' => 'Standard and cabotage permits <&>'],
            ],
            'bilateral-standard-and-cabotage--no-additional-info' => [
                'answers-summary/bilateral-standard-and-cabotage',
                ['yesNo' => 'No', 'additionalInfo' => null],
            ],
            'ecmt-no-of-permits' => [
                'answers-summary/ecmt-no-of-permits',
                ['periodNameKey' => null, 'validityYear' => 2027, 'emissionsCategories' => [
                    ['key' => 'Euro 5 <&>', 'count' => 2],
                    ['key' => 'Euro 6', 'count' => 0],
                    ['key' => 'Euro 6', 'count' => 4],
                ]],
            ],
            'ecmt-no-of-permits--period-name' => [
                'answers-summary/ecmt-no-of-permits',
                ['periodNameKey' => 'Short-term permits <&>', 'validityYear' => 2027, 'emissionsCategories' => []],
            ],
            'ecmt-restricted-countries' => [
                'answers-summary/ecmt-restricted-countries',
                ['hasRestrictedCountries' => true, 'countryNames' => ['Greece', 'Hungary <&>']],
            ],
            'ecmt-restricted-countries--none' => [
                'answers-summary/ecmt-restricted-countries',
                ['hasRestrictedCountries' => false, 'countryNames' => []],
            ],
            'multilateral-permits-required' => [
                'answers-summary/multilateral-permits-required',
                ['rows' => [['permitsRequired' => 3, 'year' => 2026], ['permitsRequired' => 7, 'year' => 2027]]],
            ],
        ];
    }

    /**
     * Variables for one email template: every `$this->name` the template's files read, set to a recognisable
     * placeholder, then hand-written values where a template loops, branches or formats.
     *
     * @param list<string> $templateFiles every file (locale and format) of the template
     *
     * @return array<string, mixed>
     */
    public static function emailVariables(string $template, array $templateFiles): array
    {
        $variables = [];

        foreach ($templateFiles as $file) {
            // Variables, not helpers: a name followed by ( is a helper call
            preg_match_all(
                '/\$this->([a-zA-Z_][a-zA-Z0-9_]*)\b(?!\s*\()/',
                (string) file_get_contents($file),
                $matches
            );

            foreach ($matches[1] as $name) {
                $variables[$name] = self::placeholder($name);
            }
        }

        ksort($variables);

        return array_merge($variables, self::emailOverrides()[$template] ?? []);
    }

    public static function placeholder(string $name): string
    {
        return '[' . $name . ' <&>]';
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function emailOverrides(): array
    {
        return [
            'continuation-not-sought' => [
                'licences' => [
                    ['licNo' => 'OB1234567', 'taName' => 'North East of England <&>'],
                    ['licNo' => 'PD7654321', 'taName' => 'Wales'],
                ],
            ],
            'digital-continuation' => ['isPsv' => false, 'isSpecialRestricted' => false, 'feeAmount' => '401.5'],
            'ebsr-data-error-list' => ['submissionErrors' => ['Missing service number <&>', 'Unknown stop']],
            'ebsr-data-error-start' => ['hasBusData' => true],
            'email-duplicate-vehicles-removal' => [
                'removedVehicles' => [
                    ['vrm' => 'AB12CDE', 'licNo' => 'OB1234567 <&>'],
                    ['vrm' => 'XY34ZZZ', 'licNo' => 'PD7654321'],
                ],
            ],
            'erru-failure' => ['errorMessages' => ['Business case not found <&>', 'Member state rejected']],
            'last-tm-operator-notification' => ['registeredToSelfserve' => false],
            'transport-manager-complete-digital-form' => ['isNi' => false],
            'inspection-request' => self::inspectionRequest(),
        ];
    }

    /**
     * As SendInspectionRequest::populateInspectionRequestVariables() builds them; the template reads them as
     * plain variables, not through $this.
     *
     * @return array<string, mixed>
     */
    private static function inspectionRequest(): array
    {
        $address = ['addressLine1' => '1 Test Street <&>', 'town' => 'Leeds', 'postcode' => 'LS1 1AA'];

        return [
            'inspectionRequestId' => 1001,
            'currentUserName' => 'caseworker',
            'currentUserEmail' => 'caseworker@example.com',
            'inspectionRequestDateRequested' => '01/10/2026 09:30:00',
            'inspectionRequestNotes' => 'Please inspect <&>',
            'inspectionRequestDueDate' => '15/10/2026 00:00:00',
            'ocAddress' => $address,
            'inspectionRequestType' => 'New application',
            'licenceNumber' => 'OB1234567',
            'licenceType' => 'Standard National',
            'totAuthVehicles' => 5,
            'totAuthTrailers' => 2,
            'numberOfOperatingCentres' => 1,
            'expiryDate' => '31/03/2030',
            'operatorId' => 30,
            'operatorName' => 'Test Haulage <&> Ltd',
            'operatorEmail' => 'operator@example.com',
            'operatorAddress' => $address,
            'contactPhoneNumbers' => [
                ['phoneNumber' => '0113 000 0000', 'phoneContactType' => ['description' => 'Business']],
            ],
            'transportManagers' => ['Jane Smith', 'John Jones'],
            'tradingNames' => ['Test Trading <&>'],
            'workshopIsExternal' => true,
            'safetyInspectionVehicles' => 6,
            'safetyInspectionTrailers' => 8,
            'inspectionProvider' => ['address' => $address],
            'people' => [['forename' => 'Jane', 'familyName' => 'Smith <&>']],
            'otherLicences' => [],
            'applicationOperatingCentres' => [
                [
                    'operatingCentre' => ['address' => $address],
                    'noOfVehiclesRequired' => 3,
                    'noOfTrailersRequired' => 1,
                    'action' => 'Added',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function review(): array
    {
        return [
            'reviewTitle' => 'application-review-title-gv',
            // Printed raw by the layout
            'subTitle' => 'Test Haulage <&> Ltd OB1234567/1001',
            'settings' => [],
            'sections' => [
                [
                    'header' => 'review-type-of-licence',
                    'config' => [
                        'multiItems' => [
                            [
                                ['label' => 'Operator location', 'value' => 'Great Britain <&>'],
                                ['label' => 'Licence type', 'value' => 'Standard', 'class' => 'extra-class'],
                                [
                                    'label' => 'Website',
                                    'value' => 'Example <&>',
                                    'url' => 'https://example.com/?a=1&b=2',
                                ],
                                [
                                    'label' => 'Raw',
                                    'value' => '<em>not escaped</em>',
                                    'noEscape' => true,
                                    'suffix' => ' (suffix)',
                                ],
                            ],
                            [
                                ['full-content' => '<p>Full content <&></p>'],
                                ['list' => ['First <&>', 'Second']],
                            ],
                        ],
                    ],
                ],
                [
                    'header' => 'review-business-details',
                    'config' => [
                        'header' => 'Business details <&>',
                        'items' => [['label' => 'Company name', 'value' => 'Test Haulage <&> Ltd']],
                        'freetext' => 'Free text, printed raw <&>',
                        'markup' => '<p>Markup, printed raw</p>',
                    ],
                ],
                [
                    'header' => 'review-conditions-undertakings',
                    'config' => [
                        'partial' => 'continuation-conditions-undertakings',
                        'variables' => [
                            'isPsvRestricted' => true,
                            'conditionsUndertakings' => [
                                'licence' => [
                                    'conditions' => [['notes' => 'Licence condition <&>']],
                                    'undertakings' => [['notes' => 'Licence undertaking']],
                                ],
                                'operatingCentres' => [
                                    [
                                        'conditions' => [[
                                            'notes' => 'Centre condition <&>',
                                            'address' => [
                                                'addressLine1' => '1 Test Street',
                                                'town' => 'Leeds',
                                                'postcode' => 'LS1 1AA',
                                            ],
                                        ]],
                                        'undertakings' => [['notes' => 'Centre undertaking']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'header' => 'review-sub-sections',
                    'hide-count' => true,
                    'config' => [
                        'subSections' => [
                            [
                                'title' => 'Sub section <&>',
                                'mainItems' => [['items' => [['label' => 'One', 'value' => '1']]]],
                            ],
                            ['config' => ['items' => [['label' => 'Two', 'value' => '2']]]],
                        ],
                    ],
                ],
                [
                    'header' => '',
                    'config' => [
                        'mainItems' => [
                            ['header' => 'Main item <&>', 'items' => [['label' => 'Three', 'value' => '3']]],
                            ['freetext' => 'No items'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function continuationReview(): array
    {
        return [
            'subTitle' => 'OB1234567 <&>',
            'reviewTitle' => 'continuation-review-title',
            'settings' => [],
            'sections' => [
                [
                    'header' => 'continuation-review-details',
                    'config' => [
                        [['value' => 'Licence number', 'header' => true], ['value' => 'OB1234567 <&>']],
                        [['value' => 'Raw', 'header' => true], ['value' => '<b>not escaped</b>', 'noEscape' => true]],
                    ],
                ],
                [
                    'header' => 'continuation-review-vehicles',
                    'config' => [
                        [
                            ['value' => 'VRM', 'header' => true],
                            ['value' => 'Weight', 'header' => true],
                            ['value' => 'Added', 'header' => true],
                        ],
                        [['value' => 'AB12CDE'], ['value' => '3500 <&>'], ['value' => '01/01/2026']],
                    ],
                    'summary' => [[['value' => 'Total', 'header' => true], ['value' => '1']]],
                    'summaryHeader' => 'continuation-review-summary <&>',
                ],
                [
                    'header' => 'continuation-review-people',
                    'config' => ['emptyTableMessage' => 'No people, printed raw <&>'],
                ],
                [
                    'header' => 'continuation-review-finance',
                    'config' => ['mainItems' => [['items' => [['label' => 'Balance', 'value' => '£1,000 <&>']]]]],
                ],
                ['header' => 'continuation-review-declaration'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function permitApplication(bool $qa): array
    {
        $questions = $qa
            ? [
                ['question' => 'Licence <&>', 'answer' => 'OG1234567<br>North East of England'],
                ['question' => 'Countries', 'answer' => 'Norway, Morocco &amp; Turkey'],
            ]
            : [
                ['question' => 'Licence <&>', 'answer' => 'OG1234567 <&>', 'questionType' => self::STRING],
                ['question' => 'Cabotage', 'answer' => true, 'questionType' => Question::QUESTION_TYPE_BOOLEAN],
                ['question' => 'Euro 6', 'answer' => 0, 'questionType' => Question::QUESTION_TYPE_BOOLEAN],
                ['question' => 'Permits required', 'answer' => '12', 'questionType' => Question::QUESTION_TYPE_INTEGER],
                ['question' => 'Countries', 'answer' => ['Austria', 'Belgium <&>'], 'questionType' => self::STRING],
                [
                    'question' => 'Unescaped',
                    'answer' => '<b>bold</b>',
                    'questionType' => self::STRING,
                    'escape' => false,
                ],
            ];

        return [
            'permitType' => 'Annual ECMT <&>',
            'operator' => 'Test Haulage <&> Ltd',
            'ref' => 'OG1234567 / IRHP1001',
            'questionAnswerPartialName' => $qa ? 'question-answer-section-qa' : 'question-answer-section',
            'questionAnswerData' => $questions,
            'guidanceDeclaration' => [
                'title' => 'Declaration <&>',
                'bullets' => 'markup-irhp-declaration-2',
                'declaration' => 'I confirm <&>',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function conversation(): array
    {
        $conversation = new MessagingConversation();
        $conversation->setSubject('Licence query <&>');

        $operator = new User('operator-pid', User::USER_TYPE_OPERATOR);
        $operator->setLoginId('operator <&>');

        $person = new Person();
        $person->setForename('Jane');
        $person->setFamilyName('Smith <&>');
        $contactDetails = new ContactDetails(new RefData(ContactDetails::CONTACT_TYPE_USER));
        $contactDetails->setPerson($person);
        $team = new Team();
        $team->setName('Licensing team');
        $caseworker = new User('caseworker-pid', User::USER_TYPE_INTERNAL);
        $caseworker->setLoginId('caseworker');
        $caseworker->setContactDetails($contactDetails);
        $caseworker->setTeam($team);

        $read = new MessagingUserMessageRead();
        $read->setUser($operator);
        $read->setCreatedOn(new \DateTime('2026-06-02T08:15:00+00:00'));

        $fromOperator = self::message($operator, "First line <&>\nSecond line", '2026-01-15T10:00:00+00:00');
        $fromCaseworker = self::message($caseworker, 'Reply from the caseworker', '2026-06-01T16:45:00+00:00');
        $fromCaseworker->addUserMessageReads($read);

        return ['conversation' => $conversation, 'messages' => [$fromOperator, $fromCaseworker], 'enhanced' => true];
    }

    private static function message(User $createdBy, string $text, string $createdOn): MessagingMessage
    {
        $content = new MessagingContent();
        $content->setText($text);

        $message = new MessagingMessage();
        $message->setMessagingContent($content);
        $message->setCreatedBy($createdBy);
        $message->setCreatedOn(new \DateTime($createdOn));

        return $message;
    }

    /**
     * @return array<string, mixed>
     */
    private static function checkReputeResponse(): array
    {
        return [
            'tmName' => 'Jane Smith <&>',
            'searchDetails' => ['Family name' => 'Smith <&>', 'Date of birth' => '01/02/1970'],
            'foundCountries' => [
                [
                    'name' => 'France <&>',
                    'details' => ['Licence' => 'FR123', 'Status' => 'Valid'],
                    'undertakings' => [['Name' => 'Transports <&> SA'], ['Name' => 'Logistique SARL']],
                ],
            ],
            'notFoundCountries' => 'Germany, Spain <&>',
        ];
    }
}
