<?php

namespace Olcs\Controller\Traits;

/**
 * Notes Processing Trait
 */
trait NotesProcessingTrait
{
    /**
     * Alter table
     *
     * @param \Olcs\Controller\Table $table
     * @param array $data
     * @return \Olcs\Controller\Table
     */
    protected function alterTable($table, $data)
    {
        $this->updateTableActionWithQuery($table);
        return $table;
    }

    /**
     * Alter form for add
     *
     * @param Form $form
     * @param array $data
     * @return Form
     */
    protected function alterFormForAdd($form, $data)
    {
        $this->getServiceLocator()->get('Helper\Form')->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }

    /**
     * Alter form for edit
     *
     * @param Form $form
     * @param array $data
     * @return Form
     */
    protected function alterFormForEdit($form, $data)
    {
        $this->getServiceLocator()->get('Helper\Form')->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }
}
