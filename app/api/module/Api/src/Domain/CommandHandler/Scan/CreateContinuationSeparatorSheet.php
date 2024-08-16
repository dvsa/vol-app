<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Scan;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\System\SubCategoryDescription;

/**
 * CreateContinuationSeparatorSheet
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CreateContinuationSeparatorSheet extends AbstractCommandHandler
{
    protected $repoServiceName = 'Scan';

    public function handleCommand(CommandInterface $command)
    {
        $result = $this->handleSideEffect(
            \Dvsa\Olcs\Transfer\Command\Scan\CreateSeparatorSheet::create(
                [
                    'categoryId' => Category::CATEGORY_LICENSING,
                    'subCategoryId' => SubCategory::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
                    'entityIdentifier' => $command->getLicNo(),
                    'descriptionId' => SubCategoryDescription::CONTINUATIONS_AND_RENEWALS_LICENCE_CHECKLIST,
                ]
            )
        );

        return $result;
    }
}
