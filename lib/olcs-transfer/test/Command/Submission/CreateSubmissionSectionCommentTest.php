<?php

namespace Dvsa\OlcsTest\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionSectionComment;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;

class CreateSubmissionSectionCommentTest extends \PHPUnit\Framework\TestCase
{
    use CommandTest;

    protected function createBlankDto()
    {
        return new CreateSubmissionSectionComment();
    }

    protected function getOptionalDtoFields()
    {
        return [];
    }

    protected function getValidFieldValues()
    {
        return [
            'submission' => ['1', '99', '12345'],
            'submissionSection' => [
                'introduction',
                'case-summary',
                'case-outline',
                'most-serious-infringement',
                'outstanding-applications',
                'people',
                'operating-centres',
                'conditions-and-undertakings',
                'intelligence-unit-check',
                'interim',
                'advertisement',
                'linked-licences-app-numbers',
                'lead-tc-area',
                'current-submissions',
                'auth-requested-applied-for',
                'transport-managers',
                'continuous-effective-control',
                'fitness-and-repute',
                'previous-history',
                'bus-reg-app-details',
                'transport-authority-comments',
                'total-bus-registrations',
                'local-licence-history',
                'linked-mlh-history',
                'registration-details',
                'maintenance-tachographs-hours',
                'prohibition-history',
                'conviction-fpn-offence-history',
                'annual-test-history',
                'penalties',
                'statements',
                'other-issues',
                'te-reports',
                'site-plans',
                'planning-permission',
                'applicants-comments',
                'visibility-access-egress-size',
                'compliance-complaints',
                'environmental-complaints',
                'oppositions',
                'financial-information',
                'maps',
                'waive-fee-late-fee',
                'surrender',
                'annex',
                'tm-details',
                'tm-qualifications',
                'tm-responsibilities',
                'tm-other-employment',
                'tm-previous-history',
                'applicants-responses'
            ],
            'comment' => [
                '{"blocks":[{"type":"paragraph","data":{"text":"Valid EditorJS JSON"}}],"version":"2.22.2"}',
                '{"blocks":[],"version":"2.22.2"}',
                '{"test":"value"}' // Any valid JSON
            ]
        ];
    }

    protected function getInvalidFieldValues()
    {
        return [
            'submission' => ['0', 'abc', null, ''],
            'submissionSection' => [
                'invalid-section',
                'not-allowed',
                '',
                null,
                123
            ],
            'comment' => [
                'invalid json',
                '{"invalid": json}',
                'abcd', // Less than 5 chars
                null,
                ''
            ]
        ];
    }

    protected function getFilterTransformations()
    {
        return [
            'submission' => [['123', '123'], ['456', '456']],
            'submissionSection' => [
                ['  introduction  ', 'introduction'],
                ['  case-summary  ', 'case-summary']
            ],
            'comment' => [
                ['  {"blocks":[]}  ', '{"blocks":[]}']
            ]
        ];
    }
}
