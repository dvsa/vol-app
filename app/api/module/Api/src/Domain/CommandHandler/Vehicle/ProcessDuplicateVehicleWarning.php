<?php

/**
 * Process Duplicate Vehicle Warning
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue;
use Dvsa\Olcs\Transfer\Command\Document\PrintLetter;
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
    protected const TEMPLATE_ID_GB = 1064;
    protected const TEMPLATE_ID_NI = 1065;
    protected $repoServiceName = 'LicenceVehicle';

    #[\Override]
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
        $adminEmails = array_filter(
            $licenceVehicle->getLicence()->getOrganisation()->getAdminEmailAddresses()
        );
        $method = !empty($adminEmails)
            ? PrintLetter::METHOD_EMAIL
            : PrintLetter::METHOD_PRINT_AND_POST;

        $this->result->merge($this->handleSideEffect(PrintLetter::create([
            'id' => $documentId,
            'method' => $method
        ])));

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
            'isExternal'  => false,
            'dispatch' => true,
            'metadata' => json_encode([
                'details' => [
                    'category'            => Category::CATEGORY_LICENSING,
                    'documentSubCategory' => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
                    'documentTemplate'    => $licenceVehicle->getLicence()->getNiFlag() === 'Y'
                        ? self::TEMPLATE_ID_NI
                        : self::TEMPLATE_ID_GB,
                    'allowEmail'          => $licenceVehicle->getLicence()->getOrganisation()->getAllowEmail(),
                ]]),
        ];

        $result = $this->handleSideEffect(GenerateAndStore::create($dtoData));

        $this->result->merge($result);

        return $result->getId('document');
    }
}
