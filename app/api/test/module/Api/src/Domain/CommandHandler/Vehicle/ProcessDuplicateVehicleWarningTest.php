<?php

declare(strict_types=1);

/**
 * Process Duplicate Vehicle Warning Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Transfer\Command\Document\PrintLetter;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle\ProcessDuplicateVehicleWarning;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\ProcessDuplicateVehicleWarning as Cmd;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;

/**
 * Process Duplicate Vehicle Warning Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ProcessDuplicateVehicleWarningTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ProcessDuplicateVehicleWarning();

        $this->mockRepo('LicenceVehicle', Repository\LicenceVehicle::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['id' => 111]);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->shouldReceive('getNiFlag')->andReturn(false);

        /** @var Vehicle $vehicle */
        $vehicle = m::mock(Vehicle::class)->makePartial();
        $vehicle->setid(333);

        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle->setLicence($licence);
        $licenceVehicle->setVehicle($vehicle);
        $licenceVehicle->setId(111);

        /** @var Organisation $organisation */
        $organisation = m::mock(Organisation::class)->makePartial();
        $organisation->shouldReceive('getAllowEmail')->andReturn(true);
        $licence->setOrganisation($organisation);

        /** @var ContactDetails $contactDetails */
        $contactDetails = m::mock(ContactDetails::class)->makePartial();
        $contactDetails->shouldReceive('getEmailAddress')->andReturn(null);
        $licence->setCorrespondenceCd($contactDetails);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licenceVehicle)
            ->shouldReceive('save')
            ->once()
            ->with($licenceVehicle);

        $result1 = new Result();
        $result1->addMessage('GenerateAndStore');
        $result1->addId('document', 12345);
        $data = [
            'template' => 'GV_Duplicate_vehicle_letter',
            'query' => ['licence' => 222, 'vehicle' => 333],
            'description' => 'Duplicate vehicle letter',
            'licence'     => 222,
            'category'    => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'isExternal'  => false,
            'dispatch' => true,
            'metadata' => json_encode([
                'details' => [
                    'category'            => Category::CATEGORY_LICENSING,
                    'documentSubCategory' => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
                    'documentTemplate'    => ProcessDuplicateVehicleWarning::TEMPLATE_ID_GB, // adjust based on niFlag, or mock getNiFlag()
                    'allowEmail'          => true,
                ]]),
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $data, $result1);

        $result2 = new Result();
        $result2->addMessage('PrintLetter');
        $data = [
            'id' => 12345,
            'method' => PrintLetter::METHOD_PRINT_AND_POST,
        ];
        $this->expectedSideEffect(PrintLetter::class, $data, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => 12345
            ],
            'messages' => [
                'GenerateAndStore',
                'PrintLetter',
                'Licence vehicle ID: 111 duplication letter sent'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
        $today = new DateTime();
        $this->assertEquals($today->format('Y-m-d'), $licenceVehicle->getWarningLetterSentDate()->format('Y-m-d'));
    }
}
