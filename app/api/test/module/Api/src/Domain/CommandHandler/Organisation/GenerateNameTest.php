<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Organisation;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Cache\ClearForOrganisation;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Organisation\GenerateName;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Organisation\GenerateName::class)]
class GenerateNameTest extends AbstractCommandHandlerTestCase
{
    public const PERSON_ID = 8001;
    public const APP_ID = 9001;
    public const ORG_ID = 7001;

    /** @var  GenerateName */
    protected $sut;

    /** @var  m\MockInterface */
    private $mockAppRepo;
    /** @var  Repository\Organisation | m\MockInterface */
    private $mockOrgRepo;

    /** @var  Entity\Organisation\Organisation | m\MockInterface */
    private $mockOrg;
    /** @var  Entity\Application\Application | m\MockInterface */
    private $mockApp;

    public function setUp(): void
    {
        $this->sut = new GenerateName();

        //  mock Entity
        $this->mockOrg = m::mock(Entity\Organisation\Organisation::class)->makePartial();
        $this->mockOrg->shouldReceive('getId')->withNoArgs()->andReturn(self::ORG_ID);

        $this->mockApp = m::mock(Entity\Application\Application::class)->makePartial();
        $this->mockApp
            ->setId(self::APP_ID);

        //  mock Repos
        $this->mockAppRepo = $this->mockRepo('Application', Repository\Application::class);
        $this->mockAppRepo->shouldReceive('fetchById')->with(self::APP_ID)->andReturn($this->mockApp);

        $this->mockOrgRepo = $this->mockRepo('Organisation', Repository\Organisation::class);
        $this->mockOrgRepo->shouldReceive('fetchById')->with(self::ORG_ID)->andReturn($this->mockOrg);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Entity\Organisation\Organisation::ORG_TYPE_REGISTERED_COMPANY,
            Entity\Organisation\Organisation::ORG_TYPE_LLP,
            Entity\Organisation\Organisation::ORG_TYPE_PARTNERSHIP,
            Entity\Organisation\Organisation::ORG_TYPE_SOLE_TRADER,
        ];

        parent::initReferences();
    }

    public function testGenerateNameNull(): void
    {
        $cmd = TransferCmd\Organisation\GenerateName::create(['organisation' => self::ORG_ID]);

        $this->mockOrg
            ->setType($this->refData[Entity\Organisation\Organisation::ORG_TYPE_SOLE_TRADER])
            ->setOrganisationPersons(new ArrayCollection());

        $this->mockOrgRepo->shouldReceive('save')->never();

        $actual = $this->sut->handleCommand($cmd);

        static::assertEquals(['Unable to generate name'], $actual->getMessages());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGenerateName')]
    public function testGenerateName(mixed $type, mixed $orgPersons, mixed $expect): void
    {
        $cmd = TransferCmd\Organisation\GenerateName::create(['organisation' => self::ORG_ID]);

        $this->mockOrg
            ->setType($this->refData[$type])
            ->setOrganisationPersons(new ArrayCollection($orgPersons));

        $this->mockOrgRepo
            ->shouldReceive('save')
            ->andReturnUsing(
                function (Entity\Organisation\Organisation $org) use ($expect) {
                    static::assertEquals($expect, $org->getName());
                }
            );

        $this->expectedSideEffect(ClearForOrganisation::class, ['id' => self::ORG_ID], new Result());
        $this->sut->handleCommand($cmd);
    }

    public static function dpTestGenerateName(): array
    {
        return [
            'ST' => [
                'type' => Entity\Organisation\Organisation::ORG_TYPE_SOLE_TRADER,
                'orgPersons' => [
                    self::getMockOrgPerson('unit_Fist', 'unit_Last'),
                ],
                'expect' => 'unit_Fist unit_Last',
            ],
            'Partner x 1' => [
                'type' => Entity\Organisation\Organisation::ORG_TYPE_PARTNERSHIP,
                'orgPersons' => [
                    self::getMockOrgPerson('unit_Fist', 'unit_Last'),
                ],
                'expect' => 'unit_Fist unit_Last',
            ],
            'Partner x 2' => [
                'type' => Entity\Organisation\Organisation::ORG_TYPE_PARTNERSHIP,
                'orgPersons' => [
                    self::getMockOrgPerson('unit_Fist', 'unit_Last'),
                    self::getMockOrgPerson('unit_Fist2', 'unit_Last2'),
                ],
                'expect' => 'unit_Fist unit_Last & unit_Fist2 unit_Last2',
            ],
            'Partner > 2' => [
                'type' => Entity\Organisation\Organisation::ORG_TYPE_PARTNERSHIP,
                'orgPersons' => [
                    self::getMockOrgPerson('unit_Fist', 'unit_Last'),
                    self::getMockOrgPerson('unit_Fist2', 'unit_Last2'),
                    self::getMockOrgPerson('unit_Fist3', 'unit_Last3'),
                ],
                'expect' => 'unit_Fist unit_Last & Partners',
            ],
        ];
    }

    private static function getMockOrgPerson(mixed $firstName, mixed $lastName): Entity\Organisation\OrganisationPerson
    {
        $mockPerson = new Entity\Person\Person();
        $mockPerson
            ->setForename($firstName)
            ->setFamilyName($lastName);

        $mockRel = new Entity\Organisation\OrganisationPerson();
        $mockRel->setPerson($mockPerson);

        return $mockRel;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleOk')]
    public function testHandleOk(mixed $cmdData): void
    {
        $cmd = TransferCmd\Organisation\GenerateName::create($cmdData);

        $orgPersons = new ArrayCollection(
            [
                $this->getMockOrgPerson('unit_First', 'unit_Last'),
            ]
        );
        $this->mockOrg
            ->setType($this->refData[Entity\Organisation\Organisation::ORG_TYPE_SOLE_TRADER])
            ->setOrganisationPersons($orgPersons);

        $this->mockOrgRepo
            ->shouldReceive('save')
            ->andReturnUsing(
                function (Entity\Organisation\Organisation $org) {
                    static::assertEquals('unit_First unit_Last', $org->getName());
                }
            );

        $this->expectedSideEffect(ClearForOrganisation::class, ['id' => self::ORG_ID], new Result());
        $actual = $this->sut->handleCommand($cmd);

        static::assertEquals(['Name succesfully generated'], $actual->getMessages());
    }

    public static function dpTestHandleOk(): array
    {
        return [
            [
                'cmdData' => [
                    'organisation' => self::ORG_ID,
                    'application' => self::APP_ID,
                ],
            ],
            [
                'cmdData' => [
                    'organisation' => self::ORG_ID,
                ],
            ],
        ];
    }

    public function testHandleFailAppIsVariation(): void
    {
        $this->expectException(ValidationException::class);

        $this->mockApp->setIsVariation(true);

        $this->sut->handleCommand(
            TransferCmd\Organisation\GenerateName::create(
                [
                    'organisation' => self::ORG_ID,
                    'application' => self::APP_ID,
                ]
            )
        );
    }

    public function testHandleFailNotSoleTraiderOrPartnership(): void
    {
        $this->expectException(ValidationException::class);

        $this->mockOrg->setType($this->refData[Entity\Organisation\Organisation::ORG_TYPE_REGISTERED_COMPANY]);

        $this->sut->handleCommand(
            TransferCmd\Organisation\GenerateName::create(
                [
                    'organisation' => self::ORG_ID,
                ]
            )
        );
    }
}
