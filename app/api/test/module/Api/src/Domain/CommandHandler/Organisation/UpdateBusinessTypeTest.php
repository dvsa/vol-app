<?php

declare(strict_types=1);

/**
 * Update Business Type Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Organisation;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Organisation\ChangeBusinessType;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Organisation\UpdateBusinessType;
use Dvsa\Olcs\Api\Domain\Repository\Organisation;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Organisation\UpdateBusinessType as Cmd;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Command\Result;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Update Business Type Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateBusinessTypeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateBusinessType();
        $this->mockRepo('Organisation', Organisation::class);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY,
            OrganisationEntity::ORG_TYPE_SOLE_TRADER
        ];

        parent::initReferences();
    }

    public function testHandleCommandWhenCanChangeWithoutValue(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->expectException(ValidationException::class);

        $data = [
            'id' => 11,
            'version' => 1,
            'application' => 111
        ];

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(11);
        $organisation->setType($this->refData[OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY]);

        $organisation->shouldReceive('hasInforceLicences')
            ->andReturn(false);

        $command = Cmd::create($data);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWhenCanChangeWithoutChange(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $data = [
            'id' => 11,
            'version' => 1,
            'application' => 111,
            'businessType' => OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY
        ];

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(11);
        $organisation->setType($this->refData[OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY]);

        $organisation->shouldReceive('hasInforceLicences')
            ->andReturn(false);

        $command = Cmd::create($data);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        $result1 = new Result();
        $result1->addMessage('Section updated');

        $completionData = ['id' => 111, 'section' => 'businessType'];
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $completionData, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Business type unchanged',
                'Section updated'
            ]
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWhenCantChangeWithChange1(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->expectException(ValidationException::class);

        $data = [
            'id' => 11,
            'version' => 1,
            'licence' => 111,
            'businessType' => OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY
        ];

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(11);
        $organisation->setType($this->refData[OrganisationEntity::ORG_TYPE_SOLE_TRADER]);

        $command = Cmd::create($data);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWhenCantChangeWithChange2(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->expectException(ValidationException::class);

        $data = [
            'id' => 11,
            'version' => 1,
            'variation' => 111,
            'businessType' => OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY
        ];

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(11);
        $organisation->setType($this->refData[OrganisationEntity::ORG_TYPE_SOLE_TRADER]);

        $command = Cmd::create($data);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWhenCantChangeWithChange3(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->expectException(ValidationException::class);

        $data = [
            'id' => 11,
            'version' => 1,
            'application' => 111,
            'businessType' => OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY
        ];

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(11);
        $organisation->setType($this->refData[OrganisationEntity::ORG_TYPE_SOLE_TRADER]);

        $organisation->shouldReceive('hasInforceLicences')
            ->andReturn(true);

        $command = Cmd::create($data);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWhenCantChangeWithoutChangeWithoutApplication1(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $data = [
            'id' => 11,
            'version' => 1,
            'licence' => 111,
            'businessType' => OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY
        ];

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(11);
        $organisation->setType($this->refData[OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY]);

        $command = Cmd::create($data);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Can\'t update business type'
            ]
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWhenCantChangeWithoutChangeWithoutApplication2(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $data = [
            'id' => 11,
            'version' => 1,
            'licence' => 111
        ];

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(11);
        $organisation->setType($this->refData[OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY]);

        $command = Cmd::create($data);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Can\'t update business type'
            ]
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWhenCantChangeWithoutChangeWithApplication(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $data = [
            'id' => 11,
            'version' => 1,
            'application' => 111,
            'businessType' => OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY
        ];

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(11);
        $organisation->setType($this->refData[OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY]);

        $organisation->shouldReceive('hasInforceLicences')
            ->andReturn(true);

        $command = Cmd::create($data);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        $result1 = new Result();
        $result1->addMessage('Section updated');

        $completionData = ['id' => 111, 'section' => 'businessType'];
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $completionData, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Can\'t update business type',
                'Section updated'
            ]
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWhenCanChangeWithChange(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $data = [
            'id' => 11,
            'version' => 1,
            'application' => 111,
            'businessType' => OrganisationEntity::ORG_TYPE_SOLE_TRADER
        ];

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(11);
        $organisation->setType($this->refData[OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY]);

        $organisation->shouldReceive('hasInforceLicences')
            ->andReturn(false);

        $command = Cmd::create($data);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation)
            ->shouldReceive('save')
            ->once()
            ->with($organisation);

        $this->assertSame($this->refData[OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY], $organisation->getType());

        $result1 = new Result();
        $result1->addMessage('Section updated');

        $completionData = ['id' => 111, 'section' => 'businessType'];
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $completionData, $result1);
        $this->expectedOrganisationCacheClear($organisation);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Business type updated',
                'Section updated'
            ]
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->refData[OrganisationEntity::ORG_TYPE_SOLE_TRADER], $organisation->getType());
    }

    public function testHandleCommandWhenCanChangeWithChangeLicence(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $data = [
            'id' => 11,
            'version' => 1,
            'licence' => 111,
            'businessType' => OrganisationEntity::ORG_TYPE_SOLE_TRADER
        ];

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(11);
        $organisation->setType($this->refData[OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY]);

        $organisation->shouldReceive('hasInforceLicences')
            ->andReturn(false);

        $command = Cmd::create($data);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        $this->assertSame($this->refData[OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY], $organisation->getType());

        $result1 = new Result();
        $data = [
            'id' => 11,
            'businessType' => OrganisationEntity::ORG_TYPE_SOLE_TRADER,
            'confirm' => false
        ];
        $this->expectedSideEffect(ChangeBusinessType::class, $data, $result1);
        $this->expectedOrganisationCacheClear($organisation);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Business type updated'
            ]
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }
}
