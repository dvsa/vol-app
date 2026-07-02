<?php

/**
 * People Adapter Interface
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Common\Controller\Lva\Interfaces;

use Laminas\Form\Form;

/**
 * People Adapter Interface
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
interface PeopleAdapterInterface
{
    public function addMessages();

    public function alterFormForOrganisation(Form $form, $table);

    public function alterAddOrEditFormForOrganisation(Form $form);

    public function canModify();

    public function createTable();

    public function delete($ids);

    public function restore($ids);
}
