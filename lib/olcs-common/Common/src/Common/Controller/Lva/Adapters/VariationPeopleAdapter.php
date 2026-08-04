<?php

namespace Common\Controller\Lva\Adapters;

use Dvsa\Olcs\Transfer\Command\Application\CreatePeople;
use Dvsa\Olcs\Transfer\Command\Application\DeletePeople;
use Dvsa\Olcs\Transfer\Command\Application\UpdatePeople;
use Psr\Container\ContainerInterface;

class VariationPeopleAdapter extends AbstractPeopleAdapter
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    #[\Override]
    protected function getTableConfig(): string
    {
        if (!$this->useDeltas()) {
            return 'lva-people';
        }

        return 'lva-variation-people';
    }

    /**
     * Get the backend command to create a Person
     *
     * @param array $params
     */
    #[\Override]
    protected function getCreateCommand($params): CreatePeople
    {
        $params['id'] = $this->getApplicationId();
        return CreatePeople::create($params);
    }

    /**
     * Get the backend command to update a Person
     *
     * @param array $params
     */
    #[\Override]
    protected function getUpdateCommand($params): UpdatePeople
    {
        $params['person'] = $params['id'];
        $params['id'] = $this->getApplicationId();
        return UpdatePeople::create($params);
    }

    /**
     * Get the backend command to delete a Person
     *
     * @param array $params
     */
    #[\Override]
    protected function getDeleteCommand($params): DeletePeople
    {
        $params['id'] = $this->getApplicationId();
        return DeletePeople::create($params);
    }
}
