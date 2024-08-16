<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Doc\Document;

/**
 * Print Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class PrintLicence extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    /**
     * Handle comment
     *
     * @param \Dvsa\Olcs\Transfer\Command\Licence\PrintLicence $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var LicenceEntity $licence */
        $licence = $this->getRepo()->fetchUsingId($command);

        if (!$licence instanceof LicenceEntity) {
            return null;
        }

        $dtoData = [
            'template' => $this->getTemplateId($licence),
            'query' => [
                'licence' => $licence->getId(),
            ],
            'description' => $this->getDescription($licence),
            'licence'     => $licence->getId(),
            'category'    => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'isExternal'  => false,
            'dispatch' => $command->getDispatch(),
        ];

        return $this->handleSideEffect(GenerateAndStore::create($dtoData));
    }

    /**
     * Get the Document ID for licence template
     *
     * @param LicenceEntity $licence The Licence to be printed
     *
     * @return int Document ID
     */
    private function getTemplateId(LicenceEntity $licence)
    {
        if ($licence->getNiFlag() === 'Y') {
            if ($licence->isGoods()) {
                return (RefData::APP_VEHICLE_TYPE_LGV === (string)$licence->getVehicleType())
                    ? Document::GV_LGV_LICENCE_NI
                    : Document::GV_LICENCE_NI;
            }

            if ($licence->isSpecialRestricted()) {
                return Document::PSR_SR_LICENCE_NI;
            }

            return Document::PSV_LICENCE_NI;
        }

        if ($licence->isGoods()) {
            return (RefData::APP_VEHICLE_TYPE_LGV === (string)$licence->getVehicleType())
                ? Document::GV_LGV_LICENCE_GB
                : Document::GV_LICENCE_GB;
        }

        if ($licence->isSpecialRestricted()) {
            return Document::PSR_SR_LICENCE_GB;
        }

        return Document::PSV_LICENCE_GB;
    }

    /**
     * Get the description/name for the document
     *
     * @param LicenceEntity $licence The Licence to be printed
     *
     * @return string
     */
    private function getDescription(LicenceEntity $licence)
    {
        if ($licence->isGoods()) {
            return (RefData::APP_VEHICLE_TYPE_LGV === (string)$licence->getVehicleType())
                ? 'GV Licence LGV Only'
                : 'GV Licence';
        }

        if ($licence->isSpecialRestricted()) {
            return 'PSV-SR Licence';
        }

        return 'PSV Licence';
    }
}
