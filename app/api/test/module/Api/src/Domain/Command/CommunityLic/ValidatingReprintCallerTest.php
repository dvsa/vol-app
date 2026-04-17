<?php

declare(strict_types=1);

/**
 * Validating Reprint Caller Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\ValidatingReprintCaller;

/**
 * Validating Reprint Caller Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ValidatingReprintCallerTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = ValidatingReprintCaller::create(
            [
                'licence' => 47,
                'user' => 22,
                'communityLicences' => [
                    [
                        'communityLicenceId' => 54,
                        'communityLicenceIssueNo' => 13
                    ],
                    [
                        'communityLicenceId' => 103,
                        'communityLicenceIssueNo' => 6
                    ]
                ]
            ]
        );

        $this->assertEquals(47, $command->getLicence());
        $this->assertEquals(22, $command->getUser());

        $expectedCommunityLicences = [
            [
                'communityLicenceId' => 54,
                'communityLicenceIssueNo' => 13
            ],
            [
                'communityLicenceId' => 103,
                'communityLicenceIssueNo' => 6
            ]
        ];

        $this->assertEquals($expectedCommunityLicences, $command->getCommunityLicences());
    }
}
