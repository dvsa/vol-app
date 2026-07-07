<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Validation\Validators\IsDocumentCreator;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;

class IsDocumentCreatorTest extends AbstractValidatorsTestCase
{
    /**
     * @var IsDocumentCreator
     */
    protected $sut;

    private const int DOCUMENT_ID = 123;
    private const int CURRENT_USER_ID = 456;
    private const int OTHER_USER_ID = 789;

    public function setUp(): void
    {
        $this->sut = new IsDocumentCreator();

        parent::setUp();
    }

    public function testIsValidWhenCurrentUserCreatedDocument(): void
    {
        $this->setupMockUser(self::CURRENT_USER_ID);
        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->with(self::DOCUMENT_ID)
            ->andReturn($this->getMockDocument(self::CURRENT_USER_ID));

        $this->assertTrue($this->sut->isValid(self::DOCUMENT_ID));
    }

    public function testIsInvalidWhenDifferentUserCreatedDocument(): void
    {
        $this->setupMockUser(self::CURRENT_USER_ID);
        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->with(self::DOCUMENT_ID)
            ->andReturn($this->getMockDocument(self::OTHER_USER_ID));

        $this->assertFalse($this->sut->isValid(self::DOCUMENT_ID));
    }

    public function testIsInvalidWhenDocumentHasNoCreator(): void
    {
        $this->setupMockUser(self::CURRENT_USER_ID);

        $mockDoc = m::mock(Document::class);
        $mockDoc->allows('getCreatedBy')->andReturnNull();

        $this->mockRepo(Repository\Document::class)
            ->shouldReceive('fetchById')
            ->with(self::DOCUMENT_ID)
            ->andReturn($mockDoc);

        $this->assertFalse($this->sut->isValid(self::DOCUMENT_ID));
    }

    private function getMockDocument(int $createdById): m\MockInterface
    {
        $mockCreator = m::mock(User::class);
        $mockCreator->allows('getId')->andReturn($createdById);

        $mockDoc = m::mock(Document::class);
        $mockDoc->allows('getCreatedBy')->andReturn($mockCreator);

        return $mockDoc;
    }

    private function setupMockUser(int $userId): void
    {
        $user = $this->mockUser();
        $user->allows('getId')->andReturn($userId);
    }
}
