<?php

return array(
    'variables' => array(
        'title' => 'Submissions'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'submission',
            'actions' => array(
                'add' => array('class' => 'action--primary'),
                'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'action--secondary js-require--one')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        ),
        'useQuery' => true
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'formatter' => 'HideIfClosedRadio'
        ),
        array(
            'title' => 'Submission No.',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array('submission' => $row['id'], 'action' => 'details'),
                    'submission',
                    true
                ) . '">' . $row['id'] . '</a>';
            },
            'sort' => 'id'
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($row) {
                return $row['submissionType']['description'];
            },
            'name' => 'submissionType',
        ),
        array(
            'title' => 'Sub status',
            'formatter' => function ($row) {
                return !empty($row['closedDate']) ? 'Closed' : 'Open';
            },
        ),
        array(
            'title' => 'Date created',
            'formatter' => function ($row) {
                return date(\DATETIMESEC_FORMAT, strtotime($row['createdOn']));
            },
            'sort' => 'createdOn'
        ),
        array(
            'title' => 'Date closed',
            'formatter' => 'Date',
            'name' => 'closedDate'
        ),
        array(
            'title' => 'Currently with',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Name';
                if (!empty($data['recipientUser']['contactDetails']['person'])) {
                    return $this->callFormatter($column, $data['recipientUser']['contactDetails']['person']);
                }
                if (!empty($data['createdBy']['contactDetails']['person'])) {
                    return $this->callFormatter($column, $data['createdBy']['contactDetails']['person']);
                }
                return '';
            }
        ),
        array(
            'title' => 'Date assigned',
            'formatter' => 'DateTime',
            'name' => 'assignedDate'
        ),
        array(
            'title' => 'Urgent',
            'formatter' => 'YesNo',
            'name' => 'urgent'
        )
    )
);
