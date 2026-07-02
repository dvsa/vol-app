<?php

namespace Common\FormService\Form\Lva;

/**
 * Abstract Goods Vehicles Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractGoodsVehicles extends AbstractLvaFormService
{
    protected $showShareInfo = false;

    public function getForm($table)
    {
        $form = $this->formHelper->createForm('Lva\GoodsVehicles');

        $this->formHelper->populateFormTable($form->get('table'), $table);

        $this->alterForm($form);

        if ($this->showShareInfo === false) {
            $this->formHelper->remove($form, 'shareInfo');
        }

        return $form;
    }

    abstract protected function alterForm($form);
}
