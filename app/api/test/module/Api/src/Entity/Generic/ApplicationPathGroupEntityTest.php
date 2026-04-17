<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Generic;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPath as ApplicationPathEntity;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup as Entity;

/**
 * ApplicationPathGroup Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class ApplicationPathGroupEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetActiveApplicationPath')]
    public function testGetActiveApplicationPath(mixed $applicationPaths, mixed $dateToCheck, mixed $expected): void
    {
        $sut = new Entity();
        $sut->setApplicationPaths($applicationPaths);

        $this->assertEquals($expected, $sut->getActiveApplicationPath($dateToCheck));
    }

    public static function dpGetActiveApplicationPath(): array
    {
        $inPast = new DateTime('last year');
        $lastWeek = new DateTime('-1 week');
        $yesterday = new DateTime('yesterday');
        $inFuture = new DateTime('next year');

        $applicationPathInPast = new ApplicationPathEntity();
        $applicationPathInPast->setEffectiveFrom($inPast);

        $applicationPathYesterday = new ApplicationPathEntity();
        $applicationPathYesterday->setEffectiveFrom($yesterday);

        $applicationPathInFuture = new ApplicationPathEntity();
        $applicationPathInFuture->setEffectiveFrom($inFuture);

        return [
            'only one app path - in the past' => [
                'applicationPaths' => new ArrayCollection([$applicationPathInPast]),
                'dateToCheck' => null,
                'expected' => $applicationPathInPast,
            ],
            'two app paths - both in the past' => [
                'applicationPaths' => new ArrayCollection([$applicationPathInPast, $applicationPathYesterday]),
                'dateToCheck' => null,
                'expected' => $applicationPathYesterday,
            ],
            'two app paths - both in the past - check against yesterdays date' => [
                'applicationPaths' => new ArrayCollection([$applicationPathInPast, $applicationPathYesterday]),
                'dateToCheck' => $yesterday,
                'expected' => $applicationPathYesterday,
            ],
            'two app paths - both in the past - check against last weeks date' => [
                'applicationPaths' => new ArrayCollection([$applicationPathInPast, $applicationPathYesterday]),
                'dateToCheck' => $lastWeek,
                'expected' => $applicationPathInPast,
            ],
            'three app paths - two in the past and one in the future' => [
                'applicationPaths' => new ArrayCollection(
                    [$applicationPathInPast, $applicationPathYesterday, $applicationPathInFuture]
                ),
                'dateToCheck' => null,
                'expected' => $applicationPathYesterday,
            ],
            'only one app path - in the future' => [
                'applicationPaths' => new ArrayCollection([$applicationPathInFuture]),
                'dateToCheck' => null,
                'expected' => null,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsBilateralCabotageOnly')]
    public function testIsBilateralCabotageOnly(mixed $applicationPathGroupId, mixed $expected): void
    {
        $sut = new Entity();
        $sut->setId($applicationPathGroupId);

        $this->assertEquals(
            $expected,
            $sut->isBilateralCabotageOnly()
        );
    }

    public static function dpIsBilateralCabotageOnly(): array
    {
        return [
            [Entity::BILATERALS_CABOTAGE_PERMITS_ONLY_ID, true],
            [Entity::BILATERALS_STANDARD_PERMITS_ONLY_ID, false],
            [Entity::BILATERALS_STANDARD_AND_CABOTAGE_PERMITS_ID, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsBilateralStandardOnly')]
    public function testIsBilateralStandardOnly(mixed $applicationPathGroupId, mixed $expected): void
    {
        $sut = new Entity();
        $sut->setId($applicationPathGroupId);

        $this->assertEquals(
            $expected,
            $sut->isBilateralStandardOnly()
        );
    }

    public static function dpIsBilateralStandardOnly(): array
    {
        return [
            [Entity::BILATERALS_CABOTAGE_PERMITS_ONLY_ID, false],
            [Entity::BILATERALS_STANDARD_PERMITS_ONLY_ID, true],
            [Entity::BILATERALS_STANDARD_AND_CABOTAGE_PERMITS_ID, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsBilateralStandardAndCabotage')]
    public function testIsBilateralStandardAndCabotage(mixed $applicationPathGroupId, mixed $expected): void
    {
        $sut = new Entity();
        $sut->setId($applicationPathGroupId);

        $this->assertEquals(
            $expected,
            $sut->isBilateralStandardAndCabotage()
        );
    }

    public static function dpIsBilateralStandardAndCabotage(): array
    {
        return [
            [Entity::BILATERALS_CABOTAGE_PERMITS_ONLY_ID, false],
            [Entity::BILATERALS_STANDARD_PERMITS_ONLY_ID, false],
            [Entity::BILATERALS_STANDARD_AND_CABOTAGE_PERMITS_ID, true],
        ];
    }
}
