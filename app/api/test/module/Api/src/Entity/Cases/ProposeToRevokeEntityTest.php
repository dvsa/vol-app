<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Cases;

use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke as Entity;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as PresidingTcEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Mockery as m;

/**
 * ProposeToRevoke Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class ProposeToRevokeEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Test constructor
     */
    public function testConstructorWithoutAssignedCaseWorker(): void
    {
        /** @var CasesEntity $mockCase */
        $mockCase = m::mock(CasesEntity::class);
        $reasons = ['foo'];
        /** @var PresidingTcEntity $mockPresidingTc */
        $mockPresidingTc = m::mock(PresidingTcEntity::class);
        $ptrAgreedDate = new \DateTime();

        $entity = new Entity($mockCase, $reasons, $mockPresidingTc, $ptrAgreedDate);

        $this->assertSame($mockCase, $entity->getCase());
        $this->assertSame($reasons, $entity->getReasons());
        $this->assertSame($ptrAgreedDate, $entity->getPtrAgreedDate());
        $this->assertSame(null, $entity->getAssignedCaseworker());
    }

    public function testConstructorWithAssignedCaseworker(): void
    {
        /** @var CasesEntity $mockCase */
        $mockCase = m::mock(CasesEntity::class);
        $reasons = ['foo'];
        /** @var PresidingTcEntity $mockPresidingTc */
        $mockPresidingTc = m::mock(PresidingTcEntity::class);
        $ptrAgreedDate = new \DateTime();
        /** @var User $assignedCaseWorker */
        $assignedCaseWorker = m::mock(User::class);

        $entity = new Entity($mockCase, $reasons, $mockPresidingTc, $ptrAgreedDate, $assignedCaseWorker);

        $this->assertSame($mockCase, $entity->getCase());
        $this->assertSame($reasons, $entity->getReasons());
        $this->assertSame($ptrAgreedDate, $entity->getPtrAgreedDate());
        $this->assertSame($assignedCaseWorker, $entity->getAssignedCaseworker());
    }

    public function testUpdateWithoutAssignedCaseworker(): void
    {
        /** @var CasesEntity $mockCase */
        $mockCase = m::mock(CasesEntity::class);
        $reasons = ['foo'];
        /** @var PresidingTcEntity $mockPresidingTc */
        $mockPresidingTc = m::mock(PresidingTcEntity::class);
        $ptrAgreedDate = new \DateTime();
        /** @var User $assignedCaseWorker */
        $assignedCaseWorker = m::mock(User::class);

        $entity = new Entity($mockCase, $reasons, $mockPresidingTc, $ptrAgreedDate, $assignedCaseWorker);

        $newReasons = ['bar'];
        /** @var PresidingTcEntity $newMockPresidingTc */
        $newMockPresidingTc = m::mock(PresidingTcEntity::class);
        $newPtrAgreedDate = new \DateTime();

        $entity->update($newReasons, $newMockPresidingTc, $newPtrAgreedDate);

        $this->assertSame($mockCase, $entity->getCase());
        $this->assertSame($newReasons, $entity->getReasons());
        $this->assertSame($newMockPresidingTc, $entity->getPresidingTc());
        $this->assertSame($newPtrAgreedDate, $entity->getPtrAgreedDate());
        $this->assertSame(null, $entity->getAssignedCaseworker());
    }

    public function testUpdateWithAssignedCaseworker(): void
    {
        /** @var CasesEntity $mockCase */
        $mockCase = m::mock(CasesEntity::class);
        $reasons = ['foo'];
        /** @var PresidingTcEntity $mockPresidingTc */
        $mockPresidingTc = m::mock(PresidingTcEntity::class);
        $ptrAgreedDate = new \DateTime();
        /** @var User $assignedCaseWorker */
        $assignedCaseWorker = m::mock(User::class);

        $entity = new Entity($mockCase, $reasons, $mockPresidingTc, $ptrAgreedDate, $assignedCaseWorker);

        $newReasons = ['bar'];
        /** @var PresidingTcEntity $newMockPresidingTc */
        $newMockPresidingTc = m::mock(PresidingTcEntity::class);
        $newPtrAgreedDate = new \DateTime();
        /** @var User $newAassignedCaseWorker */
        $newAssignedCaseWorker = m::mock(User::class);

        $entity->update($newReasons, $newMockPresidingTc, $newPtrAgreedDate, $newAssignedCaseWorker);

        $this->assertSame($mockCase, $entity->getCase());
        $this->assertSame($newReasons, $entity->getReasons());
        $this->assertSame($newMockPresidingTc, $entity->getPresidingTc());
        $this->assertSame($newPtrAgreedDate, $entity->getPtrAgreedDate());
        $this->assertSame($newAssignedCaseWorker, $entity->getAssignedCaseworker());
    }
}
