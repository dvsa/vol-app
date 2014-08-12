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
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.finance.bankrupt'
                    ),
                    'liquidation' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.finance.liquidation'
                    ),
                    'receivership' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.finance.receivership'
                    ),
                    'administration' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.finance.administration'
                    ),
                    'disqualified' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.finance.disqualified'
                    ),
                    'insolvencyDetails' => array(
                        'label' => $translationPrefix . '.insolvencyDetails.title',
                        'hint' => $translationPrefix . '.insolvencyDetails.hint',
                        'type' => 'financialHistoryTextarea',
                        'class' => 'long',
                        'data-container-class' => 'highlight-box',
                        'placeholder' => $translationPrefix . '.insolvencyDetails.placeholder',
                    ),
                    'file' => array(
                        'type' => 'multipleFileUpload'
                    ),
                    'insolvencyConfirmation' => array(
                        'type' => 'yesnocheckbox',
                        'label' => $translationPrefix . '.insolvencyConfirmation.title'
                    )
                )
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
