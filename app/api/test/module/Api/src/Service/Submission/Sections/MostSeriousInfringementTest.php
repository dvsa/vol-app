<?php

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
class MostSeriousInfringementTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\MostSeriousInfringement::class;

    protected $expectedResult = [
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
    public function sectionTestProvider()
    {
        $case = $this->getCase();

        $expectedResult = ['data' => ['overview' => $this->expectedResult]];

        return [
            [$case, $expectedResult],
        ];
    }

    protected function getCase()
    {
        $case = parent::getCase();

        $seriousInfringements = new ArrayCollection();

        $erruRequest = m::mock(ErruRequest::class)->makePartial();
        $erruRequest->setNotificationNumber('notificationNo');

        $si = m::mock(SeriousInfringement::class)->makePartial();
        $si->setId(66);
        $si->setCheckDate('2014-01-01');
        $si->setSiCategory($this->generateRefDataEntity('si_cat'));
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
