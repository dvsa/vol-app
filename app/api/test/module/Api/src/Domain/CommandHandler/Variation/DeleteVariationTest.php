<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\DeleteVariation as Sut;
use Dvsa\Olcs\Api\Domain\Exception\BadVariationTypeException;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepository;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\DeletePeople;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocuments;
use Dvsa\Olcs\Transfer\Command\PreviousConviction\DeletePreviousConviction;
use Dvsa\Olcs\Transfer\Command\Variation\DeleteVariation;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class DeleteVariationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('Application', ApplicationRepository::class);
        $this->sut = new Sut();
        parent::setUp();
    }

    public function testThatCommandIsTransactional(): void
    {
        $this->assertInstanceOf(TransactionedInterface::class, $this->sut);
    }

    public function testThatApplicationsAreRejected(): void
    {
        $this->createMockApplication(false, null);
        $this->expectBadVariationTypeException("Applications can not be deleted");
        $this->sut->handleCommand(DeleteVariation::create(['id' => 'DUMMY_APPLICATION_ID']));
    }

    public function testThatStandardVariationsAreRejected(): void
    {
        $this->createMockApplication(true, null);
        $this->expectBadVariationTypeException("Standard variations can not be deleted");
        $this->sut->handleCommand(DeleteVariation::create(['id' => 'DUMMY_APPLICATION_ID']));
    }

    public function testThatOtherVariationTypesAreRejected(): void
    {
        $this->createMockApplication(true, 'vtyp_foo');
        $this->expectBadVariationTypeException("Variations of type 'vtyp_foo' can not be deleted");
        $this->sut->handleCommand(DeleteVariation::create(['id' => 'DUMMY_APPLICATION_ID']));
    }

    public function testThatDirectorChangeVariationsAreDeleted(): void
    {
        $application = $this->createMockApplication(true, Application::VARIATION_TYPE_DIRECTOR_CHANGE);

        $this->expectDeletePersons($application);
        $this->expectDeleteDocuments($application);
        $this->expectDeletePreviousConvictions($application);
        $this->expectDeleteApplication($application);

        $this->assertResult(
            $this->sut->handleCommand(DeleteVariation::create(['id' => 'DUMMY_APPLICATION_ID'])),
            [
                'id' => ['application DUMMY_APPLICATION_ID' => 'DUMMY_APPLICATION_ID'],
                'messages' => ['Application with id DUMMY_APPLICATION_ID was deleted']
            ]
        );
    }

    private function createMockApplication(mixed $isVariation, mixed $variationType): mixed
    {
        /** @var Application|m\Mock $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId('DUMMY_APPLICATION_ID');
        $application->setIsVariation($isVariation);
        if ($variationType !== null) {
            $application->setVariationType(new RefData($variationType));
        }
        $this->repoMap['Application']->shouldReceive('fetchById')->with($application->getId())->andReturn(
            $application
        );
        return $application;
    }

    private function createMockApplicationOrganisationPerson(mixed $personId): m\MockInterface
    {
        return m::mock(ApplicationOrganisationPerson::class)->shouldReceive('getPerson')->with()->andReturn(
            m::mock(Person::class)->shouldReceive('getId')->with()->andReturn($personId)->getMock()
        )->getMock();
    }

    private function createMockDocument(mixed $documentId): m\MockInterface
    {
        return m::mock(Document::class)->shouldReceive('getId')->with()->andReturn($documentId)->getMock();
    }

    private function createMockConviction(mixed $convictionId): m\MockInterface
    {
        return m::mock(PreviousConviction::class)->shouldReceive('getId')->with()->andReturn($convictionId)->getMock();
    }

    private function expectDeletePersons(m\MockInterface $application): void
    {
        $application->shouldReceive('getApplicationOrganisationPersons')->with()->andReturn(
            [
                $this->createMockApplicationOrganisationPerson('DUMMY_PERSON_ID_1'),
                $this->createMockApplicationOrganisationPerson('DUMMY_PERSON_ID_2'),
            ]
        );

        $this->expectedSideEffect(
            DeletePeople::class,
            ['id' => 'DUMMY_APPLICATION_ID', 'personIds' => ['DUMMY_PERSON_ID_1', 'DUMMY_PERSON_ID_2',]],
            new Result()
        );
    }

    private function expectDeleteDocuments(m\MockInterface $application): void
    {
        $application->shouldReceive('getDocuments')->with()->andReturn(
            [$this->createMockDocument('DUMMY_DOCUMENT_ID_1'), $this->createMockDocument('DUMMY_DOCUMENT_ID_2')]
        );

        $this->expectedSideEffect(
            DeleteDocuments::class,
            ['ids' => ['DUMMY_DOCUMENT_ID_1', 'DUMMY_DOCUMENT_ID_2',]],
            new Result()
        );
    }

    private function expectDeletePreviousConvictions(m\MockInterface $application): void
    {
        $application->shouldReceive('getPreviousConvictions')->with()->andReturn(
            [$this->createMockConviction('DUMMY_CONVICTION_ID_1'), $this->createMockConviction('DUMMY_CONVICTION_ID_2')]
        );

        $this->expectedSideEffect(
            DeletePreviousConviction::class,
            ['ids' => ['DUMMY_CONVICTION_ID_1', 'DUMMY_CONVICTION_ID_2',]],
            new Result()
        );
    }

    private function expectDeleteApplication(Application $application): void
    {
        $this->repoMap['Application']->shouldReceive('delete')->once()->with($application);
    }

    private function assertResult(Result $result, mixed $data): void
    {
        $this->assertSame($data, $result->toArray());
    }

    /**
     * @param string $message
     */
    private function expectBadVariationTypeException(mixed $message): void
    {
        $this->expectException(BadVariationTypeException::class);
        $this->expectExceptionMessage($message);
    }
}
