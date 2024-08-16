<?php

/**
 * Process Duplicate Vehicle Warning
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * Process Duplicate Vehicle Warning
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ProcessDuplicateVehicleWarning extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'LicenceVehicle';

    public function handleCommand(CommandInterface $command)
    {
        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = $this->getRepo()->fetchUsingId($command);

        $description = 'Duplicate vehicle letter';
        $documentId = $this->generateDocument($licenceVehicle, $description);

        $data = [
            'documentId' => $documentId,
            'jobName' => $description
        ];
        $this->result->merge($this->handleSideEffect(Enqueue::create($data)));

        $licenceVehicle->setWarningLetterSentDate(new DateTime());
        $this->getRepo()->save($licenceVehicle);

        $this->result->addMessage('Licence vehicle ID: ' . $licenceVehicle->getId() . ' duplication letter sent');

        return $this->result;
    }

    protected function generateDocument(LicenceVehicle $licenceVehicle, $description)
    {
        $dtoData = [
            'template' => 'GV_Duplicate_vehicle_letter',
            'query' => [
                'licence' => $licenceVehicle->getLicence()->getId(),
                'vehicle' => $licenceVehicle->getVehicle()->getId()
            ],
            'description' => $description,
            'licence'     => $licenceVehicle->getLicence()->getId(),
            'category'    => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'isExternal'  => false
        ];

        $result = $this->handleSideEffect(GenerateAndStore::create($dtoData));

        $this->result->merge($result);

        return $result->getId('document');
    }
}
