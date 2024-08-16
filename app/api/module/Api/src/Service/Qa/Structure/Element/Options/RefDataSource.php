<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options;

use Dvsa\Olcs\Api\Domain\Repository\RefData as RefDataRepository;

class RefDataSource implements SourceInterface
{
    /**
     * Create service instance
     *
     *
     * @return RefDataSource
     */
    public function __construct(private readonly RefDataRepository $refDataRepo)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function populateOptionList(OptionList $optionList, array $options)
    {
        $refDatas = $this->refDataRepo->fetchByCategoryId($options['categoryId']);

        foreach ($refDatas as $refData) {
            $optionList->add(
                $refData->getId(),
                $refData->getDescription()
            );
        }
    }
}
