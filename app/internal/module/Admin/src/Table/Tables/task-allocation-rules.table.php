<?php

return array(
    'variables' => array(
        'title' => 'allocation rules'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary'),
                'edit' => array('class' => 'action--secondary js-require--one'),
                'delete' => array('class' => 'action--secondary js-require--multiple')
            )
        ),
        // This has to exist so that the title gets prepended with the document count
        'paginate' => array(
        )
    ),

    'columns' => array(
        array(
            'title' => 'Category',
            'formatter' => function ($row) {
                $url = $this->generateUrl(
                    ['id' => $row['id'], 'action' => 'edit'],
                    'admin-dashboard/task-allocation-rules'
                );
                return '<a href="'. $url . '">' . $row['category']['description'] .'</a>';
            }
        ),
        array(
            'title' => 'Criteria',
            'formatter' => 'TaskAllocationRule\Criteria',
        ),
        array(
            'title' => 'Traffic Area',
            'formatter' => function ($row) {
                if (empty($row['trafficArea']['name'])) {
                    return 'N/A';
                }
                return $row['trafficArea']['name'];
            }
        ),
        array(
            'title' => 'Team',
            'formatter' => function ($data) {
                return $data['team']['name'];
            }
        ),
        array(
            'title' => 'User',
            'name' => 'user->contactDetails->person',
            'formatter' => 'TaskAllocationRule\User',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ),
    )
);
