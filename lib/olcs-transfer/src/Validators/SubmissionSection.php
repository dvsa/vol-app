<?php

/**
 * SubmissionSection Validator
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Validators;

/**
 * SubmissionSection Validator
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class SubmissionSection extends \Laminas\Validator\InArray
{
    protected $haystack = ['introduction', 'case-summary', 'case-outline', 'most-serious-infringement',
        'outstanding-applications', 'people', 'operating-centres', 'conditions-and-undertakings',
        'intelligence-unit-check', 'interim', 'advertisement', 'linked-licences-app-numbers', 'lead-tc-area',
        'current-submissions', 'auth-requested-applied-for', 'transport-managers', 'continuous-effective-control',
        'fitness-and-repute', 'previous-history', 'bus-reg-app-details', 'transport-authority-comments',
        'total-bus-registrations', 'local-licence-history', 'linked-mlh-history', 'registration-details',
        'maintenance-tachographs-hours', 'prohibition-history', 'conviction-fpn-offence-history',
        'annual-test-history', 'penalties', 'statements', 'other-issues', 'te-reports', 'site-plans',
        'planning-permission', 'applicants-comments','applicants-responses',
        'visibility-access-egress-size', 'compliance-complaints', 'environmental-complaints', 'oppositions',
        'financial-information', 'maps', 'waive-fee-late-fee', 'surrender', 'annex', 'tm-details', 'tm-qualifications',
        'tm-responsibilities', 'tm-other-employment', 'tm-previous-history'
    ];

    protected $messageTemplates = [
        self::NOT_IN_ARRAY => 'The input is not contains an invalid submission section',
    ];
}
