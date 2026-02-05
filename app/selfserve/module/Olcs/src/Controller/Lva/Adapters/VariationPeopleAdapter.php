<?php

namespace Olcs\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\AbstractPeopleAdapter;
use Common\Service\Lva\PeopleLvaService;
use Dvsa\Olcs\Transfer\Command\Application\CreatePeople;
use Psr\Container\ContainerInterface;
use Laminas\Form\Form;

/**
 * External Variation People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VariationPeopleAdapter extends AbstractPeopleAdapter
{
    public function __construct(ContainerInterface $container, protected PeopleLvaService $peopleLvaService)
    {
        parent::__construct($container);
    }

    /**
     * Can Modify
     *
     * @return bool
     */
    #[\Override]
    public function canModify(): bool
    {
        // i.e. they *can't* modify exceptional org types
        // but can modify all others
        return $this->isExceptionalOrganisation() === false;
    }

    /**
     * Get Table Config
     *
     * @return string
     */
    #[\Override]
    protected function getTableConfig(): string
    {
        if (!$this->useDeltas()) {
            return 'lva-people';
        }
        return 'lva-variation-people';
    }

    /**
     * Alter Form For Organisation
     *
     * @param \Laminas\Form\FormInterface           $form  Form
     * @param \Common\Service\Table\TableBuilder $table Table
     *
     * @return void
     */
    #[\Override]
    public function alterFormForOrganisation(Form $form, $table): void
    {
        if ($this->canModify()) {
            parent::alterFormForOrganisation($form, $table);
            return;
        }

        $this->peopleLvaService->lockOrganisationForm($form, $table);
    }

    /**
     * Alter Add Or Edit Form For Organisation
     *
     * @param Form $form Form
     *
     * @return void
     */
    public function alterAddOrEditFormForOrganisation(Form $form)
    {
        if ($this->canModify()) {
            return;
        }

        $this->peopleLvaService->lockPersonForm($form, $this->getOrganisationType());
    }

    /**
     * Get the backend command to create a Person
     *
     * @param array $params Params
     *
     * @return \Dvsa\Olcs\Transfer\Command\Licence\CreatePeople
     */
    #[\Override]
    protected function getCreateCommand($params): CreatePeople
    {
        $params['id'] = $this->getApplicationId();
        return \Dvsa\Olcs\Transfer\Command\Application\CreatePeople::create($params);
    }

    /**
     * Get the backend command to update a Person
     *
     * @param array $params Params
     *
     * @return \Dvsa\Olcs\Transfer\Command\AbstractCommand
     */
    #[\Override]
    protected function getUpdateCommand($params): \Dvsa\Olcs\Transfer\Command\Application\UpdatePeople
    {
        $params['person'] = $params['id'];
        $params['id'] = $this->getApplicationId();
        return \Dvsa\Olcs\Transfer\Command\Application\UpdatePeople::create($params);
    }

    /**
     * Get the backend command to delete a Person
     *
     * @param array $params Params
     *
     * @return \Dvsa\Olcs\Transfer\Command\AbstractCommand
     */
    #[\Override]
    protected function getDeleteCommand($params): \Dvsa\Olcs\Transfer\Command\Application\DeletePeople
    {
        $params['id'] = $this->getApplicationId();
        return \Dvsa\Olcs\Transfer\Command\Application\DeletePeople::create($params);
    }
}
