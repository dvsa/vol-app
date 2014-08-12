<?php

$translationPrefix = 'selfserve-app-subSection-previous-history-licence-history';

return array(
    'application_previous-history_licence-history-sub-action' => array(
        'name' => 'application_previous-history_licence-history-sub-action',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden',
                    ),
                    'version' => array(
                        'type' => 'hidden',
                    ),
                    'previousLicenceType' => array(
                        'type' => 'hidden'
                    ),
                    'licNo' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '-licNo',
                        'class' => 'long'
                    ),
                    'holderName' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '-holderName',
                        'class' => 'long'
                    ),
                    'willSurrender' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '-willSurrender'
                    ),
                    'disqualificationDate' => array(
                        'type' => 'dateSelectWithEmpty',
                        'label' => $translationPrefix . '-disqualificationDate',
                        'filters' => '\Common\Form\Elements\InputFilters\DateNotInFuture'

                    ),
                    'disqualificationLength' => array(
                        'label' => $translationPrefix . '-disqualificationLength',
                        'type' => 'text',
                        'class' => 'long'
                    ),
                    'purchaseDate' => array(
                        'type' => 'dateSelectWithEmpty',
                        'label' => $translationPrefix . '-purchaseDate',
                        'filters' => '\Common\Form\Elements\InputFilters\DateNotInFuture'
                    ),
                )
            ),
            array(
                'type' => 'journey-crud-buttons',
            )
        )
    )
);
