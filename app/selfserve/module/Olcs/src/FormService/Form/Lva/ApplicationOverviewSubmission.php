<?php

namespace Olcs\FormService\Form\Lva;

/**
 * Class to create submission form at Application Overview page
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class ApplicationOverviewSubmission extends AbstractOverviewSubmission
{
    /**
     * Make changes in form
     *
     * @param \Laminas\Form\FormInterface $form   Form
     * @param array                    $data   Api data
     * @param array                    $params Parameters
     *
     * @return void
     * @inheritdoc
     */
    #[\Override]
    protected function alterForm(\Laminas\Form\FormInterface $form, array $data, array $params)
    {
        parent::alterForm($form, $data, $params);

        if ($params['isReadyToSubmit']) {
            $this->formHelper->remove($form, 'description');
        } else {
            $form->get('description')->setLabel('application.overview.submission.desc.notcomplete');
        }
    }
}
