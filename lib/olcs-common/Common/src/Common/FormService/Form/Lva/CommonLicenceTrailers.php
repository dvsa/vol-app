<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableBuilder;
use Laminas\Form\Form;
use Laminas\Http\Request;

class CommonLicenceTrailers
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    /**
     * Get form
     *
     * @param Request $request
     * @param TableBuilder $table
     * @return Form
     */
    public function getForm($request, $table)
    {
        $form = $this->formHelper->createFormWithRequest('Lva\Trailers', $request);
        $this->alterForm($form, $table);

        return $form;
    }

    /**
     * Generic form alterations
     *
     * @param Form $form
     * @param TableBuilder $table
     * @return Form
     */
    protected function alterForm($form, $table)
    {
        $form->get('table')->get('table')->setTable($table);
        $this->formHelper->remove($form, 'form-actions->saveAndContinue');

        $saveButton = $form->get('form-actions')->get('save');
        $saveButton->setAttribute('class', 'govuk-button');
        return $form;
    }
}
