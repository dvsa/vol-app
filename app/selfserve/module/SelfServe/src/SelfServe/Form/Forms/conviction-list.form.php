<?php

return array(
    'conviction-list' => array(
        'name' => 'conviction-list',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'elements' => array(
                    'hasOffence' => array(
                        'type' => 'yesNoRadio',
                        'required' => true
                    ),
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'convictionsConfirmation' => array(
                        'type' => 'checkbox',
                        'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-labelConfirm',
                        'options' => [
                            'must_be_checked' => true,
                        ],
                        'required' => true
                    ),
                )
            ),
            array(
                'name' => 'form-actions',
                'class' => 'action-buttons',
                'attributes' => array('class' => 'actions-container'),
                'options' => array(),
                'elements' => array(
                    'submit' => array(
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-buttonContinue',
                        'class' => 'action--primary large'
                    ),
                    'home' => array(
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-buttonBack',
                        'class' => 'action--secondary large'
                    )
                )
            )
        )
    )
);
