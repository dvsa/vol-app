<?php

$translationPrefix = 'application_previous-history_financial-history';

return array(
    $translationPrefix => array(
        'name' => $translationPrefix,
        'attributes' => array(
            'method' => 'post'
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'options' => array(
                    'label' => $translationPrefix . '.finance.title',
                    'hint' => $translationPrefix . '.finance.hint'
                ),
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'bankrupt' => array(
                        'type' => 'radio',
                        'label' => $translationPrefix . '.finance.bankrupt',
                        'value_options' => 'yes_no'
                    ),
                    'liquidation' => array(
                        'type' => 'radio',
                        'label' => $translationPrefix . '.finance.liquidation',
                        'value_options' => 'yes_no'
                    ),
                    'receivership' => array(
                        'type' => 'radio',
                        'label' => $translationPrefix . '.finance.receivership',
                        'value_options' => 'yes_no'
                    ),
                    'administration' => array(
                        'type' => 'radio',
                        'label' => $translationPrefix . '.finance.administration',
                        'value_options' => 'yes_no'
                    ),
                    'disqualified' => array(
                        'type' => 'radio',
                        'label' => $translationPrefix . '.finance.disqualified',
                        'value_options' => 'yes_no'
                    ),
                    'insolvencyDetails' => array(
                        'label' => $translationPrefix . '.insolvencyDetails.title',
                        'hint' => $translationPrefix . '.insolvencyDetails.hint',
                        'type' => 'financialHistoryTextarea',
                        'class' => 'long',
                        'placeholder' => $translationPrefix . '.insolvencyDetails.placeholder',
                    ),
                    'insolvencyConfirmation' => array(
                        'type' => 'multicheckbox',
                        'value_options' => array(
                            '1' => $translationPrefix . '.insolvencyConfirmation.title'
                        ),
                        'options' => array(
                            'must_be_checked' => true
                        )
                    )
                )
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
