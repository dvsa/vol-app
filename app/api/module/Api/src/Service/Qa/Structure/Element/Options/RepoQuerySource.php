<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options;

use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;

class RepoQuerySource implements SourceInterface
{
    /**
     * Create service instance
     *
     *
     * @return RepoQuerySource
     */
    public function __construct(private readonly RepositoryServiceManager $repoServiceManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function populateOptionList(OptionList $optionList, array $options)
    {
        $methodName = $options['method'];
        $items = $this->repoServiceManager->get($options['repo'])->$methodName();

        foreach ($items as $item) {
            $hint = null;
            if (isset($item['hint'])) {
                $hint = $item['hint'];
            }

            $optionList->add(
                $item['value'],
                $item['label'],
                $hint
            );
        }
    }
}
