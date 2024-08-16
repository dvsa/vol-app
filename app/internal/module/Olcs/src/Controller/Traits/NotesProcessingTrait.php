<?php

namespace Olcs\Controller\Traits;

use Common\Service\Table\TableBuilder;
use Laminas\Form\FormInterface;

/**
 * Notes Processing Trait
 */
trait NotesProcessingTrait
{
    /**
     * Alter table
     *
     * @param  TableBuilder $table
     * @param  array                  $data
     * @return TableBuilder
     */
    protected function alterTable($table, $data)
    {
        $this->updateTableActionWithQuery($table);
        return $table;
    }

    /**
     * Alter form for add
     *
     * @param  FormInterface  $form
     * @param  array $data
     * @return FormInterface
     */
    protected function alterFormForAdd($form, $data)
    {
        $this->formHelperService->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }

    /**
     * Alter form for edit
     *
     * @param  FormInterface  $form
     * @param  array $data
     * @return FormInterface
     */
    protected function alterFormForEdit($form, $data)
    {
        $this->formHelperService->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }
}
