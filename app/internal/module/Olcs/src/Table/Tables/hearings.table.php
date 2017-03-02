<?php

return array(
    'variables' => array(
        'title' => 'Hearings'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'conviction',
            'actions' => array(
                'addHearing' => array('class' => 'action--primary', 'value' => 'Add Hearing'),
                'editHearing' => array('requireRows' => true, 'value' => 'Edit Hearing')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => '&nbsp;',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Hearing Date',
            'formatter' => function ($data, $column) {

                $url = $this->generateUrl(['action' => 'edit', 'id' => $data['id']], 'case_pi', true);

                $column['formatter'] = 'Date';
                return '<a href="' . $url . '">' . date(\DATE_FORMAT, strtotime($data['hearingDate'])) . '</a>';
            },
            'name' => 'id'
        ),
        array(
            'title' => 'Is Adjourned',
            'name' => 'isAdjourned'
        ),
        array(
            'title' => 'Venue',
            'name' => 'venue'
        ),
        array(
            'title' => 'Presiding TC',
            'formatter' => function ($data) {
                return $data['presidingTc']['name'];
            }
        ),
    )
);
