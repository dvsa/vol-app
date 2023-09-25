<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\Translate;
use Common\Service\Table\Formatter\YesNo;

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
            'formatter' => Translate::class,
            'name' => 'conditionType->description'
        ),
        array(
            'title' => $translationPrefix . '.dateAdded',
            'name' => 'createdOn',
            'formatter' => Date::class
        ),
        array(
            'title' => $translationPrefix  . '.fulfilled',
            'formatter' => YesNo::class,
            'name' => 'isFulfilled'
        ),
        array(
            'title' => $translationPrefix . '.status',
            'formatter' => function ($data, $col) {
                return $this->translator->translate(
                    'common.table.status.' .
                    ($data['isDraft'] === 'Y' ? 'draft' : 'approved')
                );
            }
        )
    )
);
