<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Bus;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as Entity;

/**
 * LocalAuthority Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class LocalAuthorityEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testAuthorityWithNoSuccessorIsCurrent(): void
    {
        $darlington = $this->authority('Darlington');

        $this->assertSame([$darlington], $darlington->getCurrentAuthorities());
        $this->assertFalse($darlington->isSuperseded());
    }

    public function testReplacedAuthorityResolvesToItsSuccessor(): void
    {
        $darlington = $this->authority('Darlington');
        $teesValley = $this->authority('TeesValleyCA');
        $darlington->addSuccessors($teesValley);

        $this->assertSame([$teesValley], $darlington->getCurrentAuthorities());
        $this->assertTrue($darlington->isSuperseded());
    }

    public function testSuccessorsAreFollowedToTheEndOfTheChain(): void
    {
        $gmpte = $this->authority('GMPTE');
        $tfgm = $this->authority('TfGM');
        $gmca = $this->authority('GreaterManchesterMCA');
        $gmpte->addSuccessors($tfgm);
        $tfgm->addSuccessors($gmca);

        $this->assertSame([$gmca], $gmpte->getCurrentAuthorities());
    }

    public function testSplitAuthorityResolvesToEverySuccessor(): void
    {
        $northamptonshire = $this->authority('Northamptonshire');
        $west = $this->authority('WestNorthamptonshire');
        $north = $this->authority('NorthNorthamptonshire');
        $northamptonshire->addSuccessors($west);
        $northamptonshire->addSuccessors($north);

        $this->assertSame([$west, $north], $northamptonshire->getCurrentAuthorities());
    }

    public function testSuccessorsReachedTwiceAreReturnedOnce(): void
    {
        $old = $this->authority('Old');
        $left = $this->authority('Left');
        $right = $this->authority('Right');
        $merged = $this->authority('Merged');
        $old->addSuccessors($left);
        $old->addSuccessors($right);
        $left->addSuccessors($merged);
        $right->addSuccessors($merged);

        $this->assertSame([$merged], $old->getCurrentAuthorities());
    }

    public function testCircularSuccessorsResolveToTheAuthorityItself(): void
    {
        $first = $this->authority('First');
        $second = $this->authority('Second');
        $first->addSuccessors($second);
        $second->addSuccessors($first);

        $this->assertSame([$first], $first->getCurrentAuthorities());
    }

    public function testCurrentAuthoritiesOfSeveralAuthoritiesGivesEachOnce(): void
    {
        $darlington = $this->authority('Darlington');
        $stockton = $this->authority('StocktonOnTees');
        $teesValley = $this->authority('TeesValleyCA');
        $kent = $this->authority('Kent');
        $darlington->addSuccessors($teesValley);
        $stockton->addSuccessors($teesValley);

        $this->assertSame([$teesValley, $kent], Entity::currentAuthoritiesOf([$darlington, $stockton, $kent]));
        $this->assertSame([], Entity::currentAuthoritiesOf([]));
    }

    public function testAuthoritiesIncludingPredecessorsCoverEveryAuthorityItReplaced(): void
    {
        $teesValley = $this->authority('TeesValleyCA', 193);
        $darlington = $this->authority('Darlington', 36);
        $stockton = $this->authority('StocktonOnTees', 168);
        $older = $this->authority('Older', 7);
        $darlington->addSuccessors($teesValley);
        $teesValley->addPredecessors($darlington);
        $stockton->addSuccessors($teesValley);
        $teesValley->addPredecessors($stockton);
        $older->addSuccessors($darlington);
        $darlington->addPredecessors($older);

        $this->assertSame([193, 36, 7, 168], $this->ids($teesValley->getAuthoritiesIncludingPredecessors()));
        $this->assertSame([36, 7], $this->ids($darlington->getAuthoritiesIncludingPredecessors()));
        $this->assertSame([168], $this->ids($stockton->getAuthoritiesIncludingPredecessors()));
    }

    /**
     * Users can still be attached to a council that has been replaced; they work for the authority
     * that replaced it, so see everything a user of that authority sees
     */
    public function testAuthorityIdsVisibleToUsers(): void
    {
        $teesValley = $this->authority('TeesValleyCA', 193);
        $darlington = $this->authority('Darlington', 36);
        $stockton = $this->authority('StocktonOnTees', 168);
        $kent = $this->authority('Kent', 75);
        foreach ([$darlington, $stockton] as $council) {
            $council->addSuccessors($teesValley);
            $teesValley->addPredecessors($council);
        }

        $this->assertSame([193, 36, 168], $teesValley->getAuthorityIdsVisibleToUsers());
        $this->assertSame([193, 36, 168], $darlington->getAuthorityIdsVisibleToUsers());
        $this->assertSame([75], $kent->getAuthorityIdsVisibleToUsers());
    }

    public function testUsersIncludingPredecessors(): void
    {
        $teesValley = $this->authority('TeesValleyCA', 193);
        $darlington = $this->authority('Darlington', 36);
        $darlington->addSuccessors($teesValley);
        $teesValley->addPredecessors($darlington);

        $caUser = new \Dvsa\Olcs\Api\Entity\User\User('pid', \Dvsa\Olcs\Api\Entity\User\User::USER_TYPE_LOCAL_AUTHORITY);
        $councilUser = new \Dvsa\Olcs\Api\Entity\User\User('pid', \Dvsa\Olcs\Api\Entity\User\User::USER_TYPE_LOCAL_AUTHORITY);
        $teesValley->addUsers($caUser);
        $darlington->addUsers($councilUser);

        $this->assertSame([$caUser, $councilUser], $teesValley->getUsersIncludingPredecessors());
        $this->assertSame([$councilUser], $darlington->getUsersIncludingPredecessors());
    }

    public function testAuthoritiesIncludingPredecessorsStopAtACircle(): void
    {
        $first = $this->authority('First', 1);
        $second = $this->authority('Second', 2);
        $first->addPredecessors($second);
        $second->addPredecessors($first);

        $this->assertSame([1, 2], $this->ids($first->getAuthoritiesIncludingPredecessors()));
    }

    public function testWithCurrentAuthoritiesKeepsTheAuthoritiesGivenAndAddsTheirSuccessors(): void
    {
        $darlington = $this->authority('Darlington');
        $stockton = $this->authority('StocktonOnTees');
        $teesValley = $this->authority('TeesValleyCA');
        $kent = $this->authority('Kent');
        $darlington->addSuccessors($teesValley);
        $stockton->addSuccessors($teesValley);

        $this->assertSame(
            [$darlington, $teesValley, $stockton, $kent],
            Entity::withCurrentAuthorities([$darlington, $stockton, $kent])
        );
    }

    /**
     * @param Entity[] $authorities
     *
     * @return int[]
     */
    private function ids(array $authorities): array
    {
        return array_map(static fn (Entity $authority) => $authority->getId(), $authorities);
    }

    private function authority(string $txcName, ?int $id = null): Entity
    {
        $authority = new Entity();
        $authority->setId($id);
        $authority->setTxcName($txcName);
        $authority->setDescription($txcName);

        return $authority;
    }
}
