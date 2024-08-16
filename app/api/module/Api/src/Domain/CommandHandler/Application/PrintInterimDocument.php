<?php

/**
 * Print Interim Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\Application\PrintInterimDocument as Cmd;

/**
 * Print Interim Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class PrintInterimDocument extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    /**
     * @param Cmd $command
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);

        $this->result->merge($this->generateDocument($application));

        return $this->result;
    }

    protected function generateDocument(ApplicationEntity $application)
    {
        if ($application->isVariation()) {
            $template = 'GV_INT_DIRECTION_V1';
            $description = 'GV Interim Direction';

            if (RefData::APP_VEHICLE_TYPE_LGV === (string)$application->getVehicleType()) {
                // use different template for LGV only
                $template = 'GV_LGV_INT_DIRECTION_V1';
                $description = 'GV Interim Direction LGV Only';
            }
        } else {
            $template = 'GV_INT_LICENCE_V1';
            $description = 'GV Interim Licence';

            if (RefData::APP_VEHICLE_TYPE_LGV === (string)$application->getVehicleType()) {
                // use different template for LGV only
                $template = 'GV_LGV_INT_LICENCE_V1';
                $description = 'GV Interim Licence LGV Only';
            }
        }

        $dtoData = [
            'template' => $template,
            'query' => [
                'application' => $application->getId(),
                'licence' => $application->getLicence()->getId()
            ],
            'description' => $description,
            'application' => $application->getId(),
            'licence' => $application->getLicence()->getId(),
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'isExternal' => false,
            'isScan' => false,
            'dispatch' => true
        ];

        return $this->handleSideEffect(GenerateAndStore::create($dtoData));
    }
}
