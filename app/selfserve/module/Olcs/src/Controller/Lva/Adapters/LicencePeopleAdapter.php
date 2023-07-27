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
use Interop\Container\ContainerInterface;
use Laminas\Form\Form;

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicencePeopleAdapter extends AbstractPeopleAdapter
{
    protected PeopleLvaService $peopleLvaService;

    public function __construct(ContainerInterface $container, PeopleLvaService $peopleLvaService)
    {
        $this->peopleLvaService = $peopleLvaService;
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
    public function alterFormForOrganisation(Form $form, $table)
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
     * @return mixed
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
    public function canModify()
    {
        return !$this->isExceptionalOrganisation();
    }

    /**
     * Create the table with added button for adding person
     *
     * @return TableBuilder
     *
     */
    public function createTable()
    {
        $table = parent::createTable();
        return parent::amendLicencePeopleListTable($table);
    }

    /**
     * Get the backend command to delete a Person
     *
     * @param array $params Params
     *
     * @return AbstractCommand
     */
    protected function getDeleteCommand($params)
    {
        $params['id'] = $this->getLicenceId();
        return DeletePeopleViaVariation::create($params);
    }
}
