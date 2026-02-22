<?php

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Olcs\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\AbstractPeopleAdapter;
use Common\Service\Lva\PeopleLvaService;
use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\Licence\DeletePeopleViaVariation;
use Psr\Container\ContainerInterface;
use Laminas\Form\Form;

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicencePeopleAdapter extends AbstractPeopleAdapter
{
    public function __construct(ContainerInterface $container, protected PeopleLvaService $peopleLvaService)
    {
        parent::__construct($container);
    }

    /**
     * Alter Form For Organisation
     *
     * @param Form         $form  Form
     * @param TableBuilder $table Table
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
     * Change the Add/Edit buttons based on organisation
     *
     * @param \Laminas\Form\Form $form form
     *
     * @return void
     */
    public function alterAddOrEditFormForOrganisation(Form $form)
    {
        $this->peopleLvaService->lockPersonForm($form, $this->getOrganisationType());
    }

    /**
     * Determine if form can be modified
     *
     * @return bool
     */
    #[\Override]
    public function canModify(): bool
    {
        return !$this->isExceptionalOrganisation();
    }

    /**
     * Create the table with added button for adding person
     *
     * @return TableBuilder
     *
     */
    #[\Override]
    public function createTable(): TableBuilder
    {
        $table = parent::createTable();
        return parent::amendLicencePeopleListTable($table);
    }

    /**
     * Get the backend command to delete a Person
     *
     * @param array $params Params
     *
     * @return \Dvsa\Olcs\Transfer\Command\Licence\DeletePeople|DeletePeopleViaVariation
     */
    #[\Override]
    protected function getDeleteCommand($params): DeletePeopleViaVariation|\Dvsa\Olcs\Transfer\Command\Licence\DeletePeople
    {
        $params['id'] = $this->getLicenceId();
        return DeletePeopleViaVariation::create($params);
    }
}
