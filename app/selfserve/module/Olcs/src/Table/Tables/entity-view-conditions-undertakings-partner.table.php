<?php

$translationPrefix = 'entity-view-table-conditions-undertakings.table';

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => $translationPrefix . '.description',
            'name' => 'notes'
        ),
        array(
            'title' => $translationPrefix . '.type',
            'formatter' => 'Translate',
            'name' => 'conditionType->description'
        ),
        array(
            'title' => $translationPrefix . '.dateAdded',
            'name' => 'createdOn',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix  . '.fulfilled',
            'formatter' => 'YesNo',
            'name' => 'isFulfilled'
        ),
        array(
            'title' => $translationPrefix . '.status',
            'formatter' => function ($data, $col, $sl) {
                /** @var \Zend\I18n\Translator\Translator $translator */
                $translator = $sl->get('translator');

                return $translator->translate(
                    'common.table.status.' .
                    ($data['isDraft'] === 'Y' ? 'draft' : 'approved')
                );
            }
        )
    )
);
