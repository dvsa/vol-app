<?php

declare(strict_types=1);

/**
 * Update Statement Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Statement;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate;
use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Statement\UpdateStatement;
use Dvsa\Olcs\Api\Domain\Repository\Statement as StatementRepo;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as ContactDetailsRepo;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\Statement\UpdateStatement as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\Statement as StatementEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;

/**
 * Update Statement Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class UpdateStatementTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateStatement();
        $this->mockRepo('Statement', StatementRepo::class);
        $this->mockRepo('ContactDetails', ContactDetailsRepo::class);
        $this->mockRepo('Cases', CasesRepo::class);

        parent::setUp();
    }

    private function getPayload(): array
    {
        return [
            "id" => 99,
            "version" => 2,
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
            "id" => 99,
            "version" => 2,
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

        $this->references[LicenceEntity::class][7]->shouldReceive('getLicNo')->andReturn('12345');
        $this->references[CasesEntity::class][24]->setLicence($this->references[LicenceEntity::class][7]);

        /** @var PersonEntity $person */
        $person = m::mock(PersonEntity::class)->makePartial();
        $person->setId(44);

        /** @var AddressEntity $address */
        $address = m::mock(AddressEntity::class)->makePartial();
        $address->setId(55);

        /** @var RefDataEntity $contactType */
        $contactType = m::mock(RefDataEntity::class)->makePartial();
        $contactType->setId('ct_requestor');

        /** @var ContactDetailsEntity $contactDetails */
        $contactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $contactDetails->setId(33);
        $contactDetails->setContactType($contactType);
        $contactDetails->setPerson($person);
        $contactDetails->setAddress($address);

        /** @var StatementEntity $statement */
        $statement = m::mock(StatementEntity::class)->makePartial();
        $statement->setId(99);
        $statement->setRequestorsContactDetails($contactDetails);

        /** @var StatementEntity $se */
        $se = null;

        $this->repoMap['Statement']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($statement)
            ->once()
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
        $this->assertContains('Statement updated', $result->getMessages());
        $this->assertSame($this->references[User::class]['DUMMY_CASEWORKER_ID'], $se->getAssignedCaseworker());
    }
}
