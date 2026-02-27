<?php

namespace Olcs\FormService\Form\Lva;

use Common\RefData;

/**
 * Class to create submission form at Application Overview page
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class VariationOverviewSubmission extends AbstractOverviewSubmission
{
    protected $translationHelper;
    protected $formHelper;

    public function __construct($translationHelper, $formHelper)
    {
        parent::__construct($translationHelper, $formHelper);
    }
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

        $descText = 'variation.overview.submission.desc.notchanged';

        if ($this->hasSectionsWithStatus(RefData::VARIATION_STATUS_REQUIRES_ATTENTION)) {
            $descText = 'variation.overview.submission.desc.req-attention';
        } elseif ($this->hasSectionsWithStatus(RefData::VARIATION_STATUS_UPDATED)) {
            $descText = 'variation.overview.submission.desc.must-submit';
        }

        //  set desc text
        $form->get('description')
            ->setLabel($descText);
    }
}
