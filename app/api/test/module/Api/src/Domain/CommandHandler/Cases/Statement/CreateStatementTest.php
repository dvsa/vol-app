<?php

declare(strict_types=1);

/**
 * Create Statement Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Statement;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate;
use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Statement\CreateStatement;
use Dvsa\Olcs\Api\Domain\Repository\Statement as StatementRepo;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as ContactDetailsRepo;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\Statement\CreateStatement as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\Statement as StatementEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Create Statement Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class CreateStatementTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateStatement();
        $this->mockRepo('Statement', StatementRepo::class);
        $this->mockRepo('ContactDetails', ContactDetailsRepo::class);
        $this->mockRepo('Cases', CasesRepo::class);

        parent::setUp();
    }

    private function getPayload(): array
    {
        return [
            "case" => 24,
            "statementType" => "statement_t_36",
            "assignedCaseworker" => "DUMMY_CASEWORKER_ID",
            "vrm" => "AB12CDE",
            "requestorsContactDetails" => [
                "person" => [
                    "title" => "title_mr",
                    "forename" => "Bob",
                    "familyName" => "Smith"
                ],
                "address" => [
                    "addressLine1" => "Unit 5",
                    "addressLine2" => "12 Albert Street",
                    "addressLine3" => "Westpoint",
                    "addressLine4" => "",
                    "countryCode" => "GB",
                    "postcode" => "LS9 6NA",
                    "town" => "Leeds"
                ]
            ],
            "requestorsBody" => "REQUESTORS BODY",
            "stoppedDate" => "2015-01-05",
            "requestedDate" => "2015-05-05",
            "issuedDate" => "2015-02-02",
            "contactType" => "cm_email",
            "authorisersDecision" => "Decision is ..."
        ];
    }

    private function getReferencedPayload(): array
    {
        return [
            "case" => 24,
            "statementType" => new RefDataEntity(),
            "vrm" => "AB12CDE",
            "requestorsContactDetails" => [
                "person" => [
                    "title" => new RefDataEntity(),
                    "forename" => "Bob",
                    "familyName" => "Smith"
                ],
                "address" => [
                    "addressLine1" => "Unit 5",
                    "addressLine2" => "12 Albert Street",
                    "addressLine3" => "Westpoint",
                    "addressLine4" => "",
                    "countryCode" => new CountryEntity(),
                    "postcode" => "LS9 6NA",
                    "town" => "Leeds"
                ]
            ],
            "requestorsBody" => "REQUESTORS BODY",
            "stoppedDate" => "2015-01-05",
            "requestedDate" => "2015-05-05",
            "issuedDate" => "2015-02-02",
            "contactType" => new RefDataEntity(),
            "authorisersDecision" => "Decision is ..."
        ];
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'ct_requestor',
            'statement_t_36'
        ];

        $this->references = [
            CasesEntity::class => [
                24 => m::mock(CasesEntity::class)
            ],
            LicenceEntity::class => [
                7 => m::mock(LicenceEntity::class)
            ],
            User::class => [
                'DUMMY_CASEWORKER_ID' => m::mock(User::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $payload = $this->getPayload();
        $referencedPayload = $this->getReferencedPayload();

        $command = Cmd::create(
            $payload
        );

        $this->repoMap['ContactDetails']
            ->shouldReceive('populateRefDataReference')
            ->with($payload['requestorsContactDetails'])
            ->once()
            ->andReturn(
                $referencedPayload['requestorsContactDetails']
            );

        $this->references[LicenceEntity::class][7]->setLicenceType(LicenceEntity::LICENCE_STATUS_VALID);
        $this->references[LicenceEntity::class][7]->shouldReceive('getLicNo')->andReturn('12345');
        $this->references[CasesEntity::class][24]->setLicence($this->references[LicenceEntity::class][7]);

        /** @var StatementEntity $se */
        $se = null;

        $this->repoMap['Statement']
            ->shouldReceive('save')
            ->with(m::type(StatementEntity::class))
            ->andReturnUsing(
                function (StatementEntity $statement) use (&$se) {
                    $se = $statement;
                    $statement->setId(99);
                }
            )
            ->once();

        $this->expectedSideEffect(GenerateSlaTargetDate::class, [], new Result());
        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Statement created', $result->getMessages());
        $this->assertSame($this->references[User::class]['DUMMY_CASEWORKER_ID'], $se->getAssignedCaseworker());
    }
}
