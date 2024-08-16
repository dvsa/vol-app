<?php

/**
 * Generate Irfo Gv Permit
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;

/**
 * Generate Irfo Gv Permit
 */
final class GenerateIrfoGvPermit extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrfoGvPermit';

    public function handleCommand(CommandInterface $command)
    {
        $irfoGvPermit = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        if ($irfoGvPermit->isGeneratable() !== true) {
            throw new BadRequestException('The record is not generatable');
        }

        $template = 'IRFO_GV_'
            . str_replace(' ', '_', $irfoGvPermit->getIrfoGvPermitType()->getIrfoCountry()->getDescription());

        $description = sprintf(
            'IRFO GV Permit (%d) x %d',
            $irfoGvPermit->getId(),
            $irfoGvPermit->getNoOfCopies()
        );

        // generate document
        $this->handleSideEffect(
            GenerateAndStore::create(
                [
                    'template' => $template,
                    'query' => [
                        'irfoGvPermit' => $irfoGvPermit->getId(),
                        'organisation' => $irfoGvPermit->getOrganisation()->getId(),
                    ],
                    'knownValues' => [],
                    'description' => $description,
                    'irfoOrganisation' => $irfoGvPermit->getOrganisation()->getId(),
                    'category' => CategoryEntity::CATEGORY_IRFO,
                    'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_IRFO_CONTINUATIONS_AND_RENEWALS,
                    'isExternal' => false,
                    'isScan' => false
                ]
            )
        );

        $result = new Result();
        $result->addId('irfoGvPermit', $irfoGvPermit->getId());
        $result->addMessage('IRFO GV Permit generated successfully');

        return $result;
    }
}
