<?php

return array(
    'variables' => array(
        'action_route' => [
            'route' => 'case_pi',
            'params' => ['action' => 'index']
        ],
        'title' => 'Hearings',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('class' => 'secondary js-require--one', 'requireRows' => true),
                'generate' => array(
                    'requireRows' => true,
                    'class' => 'secondary js-require--multiple',
                    'label' => 'Generate Letter'
                ),
            ),
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
            'format' => '{{[elements/radio]}}',
            'hideWhenDisabled' => true
        ),
        array(
            'title' => 'Date of PI',
            'formatter' => function ($data) {
                $date = date('d/m/Y', strtotime($data['hearingDate']));
                if (!empty($data['pi']['closedDate'])) {
                    return $date;
                } else {
                    $url = $this->generateUrl(
                        ['action' => 'edit', 'id' => $data['id'], 'pi' => $data['pi']['id']],
                        'case_pi_hearing', true
                    );
                    return '<a href="' . $url . '" class="js-modal-ajax">' . $date . '</a>';
                }
            },
            'name' => 'id'
        ),
        array(
            'title' => 'Venue',
            'formatter' => function ($data) {
                return (isset($data['piVenue']['name']) ? $data['piVenue']['name'] : $data['piVenueOther']);
            }
        ),
        array(
            'title' => 'Adjourned',
            'name' => 'isAdjourned'
        ),
        array(
            'title' => 'Cancelled',
            'name' => 'isCancelled'
        ),
    )
);
