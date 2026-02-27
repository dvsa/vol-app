<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement;
use Dvsa\Olcs\Api\Entity\Si\SiCategoryType;
use Mockery as m;

/**
 * Class MostSeriousInfringementTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class MostSeriousInfringementTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\MostSeriousInfringement::class;

    protected const EXPECTED_RESULT = [
        'id' => 66,
        'notificationNumber' => 'notificationNo',
        'siCategory' => 'si_cat-desc',
        'siCategoryType' => 'si_cat_type-desc',
        'infringementDate' => '05/05/2014',
        'checkDate' => '01/01/2014',
        'isMemberState' => true,
    ];

    /**
     * Filter provider
     *
     * @return array
     */
    public static function sectionTestProvider(): array
    {
        $case = static::getCase();

        $expectedResult = ['data' => ['overview' => static::EXPECTED_RESULT]];

        return [
            [$case, $expectedResult],
        ];
    }

    public static function getCase(): mixed
    {
        $case = parent::getCase();

        $seriousInfringements = new ArrayCollection();

        $erruRequest = m::mock(ErruRequest::class)->makePartial();
        $erruRequest->setNotificationNumber('notificationNo');

        $si = m::mock(SeriousInfringement::class)->makePartial();
        $si->setId(66);
        $si->setCheckDate('2014-01-01');
        $si->setSiCategory(static::generateRefDataEntity('si_cat'));
        $si->setInfringementDate('2014-05-05');

        $siCategoryType = new SiCategoryType();
        $siCategoryType->setDescription('si_cat_type-desc');
        $si->setSiCategoryType($siCategoryType);

        $seriousInfringements->add($si);

        $case->setSeriousInfringements($seriousInfringements);
        $case->setErruRequest($erruRequest);

        return $case;
    }
}
