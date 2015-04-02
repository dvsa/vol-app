<?php

namespace Olcs\Data\Object\Search;

/**
 * Class User
 * @package Olcs\Data\Object\Search
 */
class User extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Users';
    /**
     * @var string
     */
    protected $key = 'user';

    /**
     * @var string
     */
    protected $searchIndices = 'user';

    /**
     * Contains an array of the instantiated filters classes.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Returns an array of filters for this index
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return [
            'crud' => [
                'actions' => [
                    'add' => ['class' => 'primary', 'requireRows' => false],
                    'edit' => ['requireRows' => true, 'class' => 'secondary js-require--one'],
                    'delete' => ['requireRows' => true, 'class' => 'secondary js-require--one']
                ]
            ],
            'paginate' => [
                'limit' => [
                    'default' => 25,
                    'options' => array(10, 25, 50)
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getColumns()
    {
         return array(
            array(
                'title' => 'Username',
                'formatter' => function ($data) {
                    return '<a href="/admin/user-management/users/edit/' . $data['userId']
                     . '">' . $data['userId'] . '</a>';
                }
            ),
            array(
                'title' => 'Name',
                'formatter' => function ($data, $column, $sm) {
                    return $data['forename'] . ' ' .
                    $data['familyName'];
                }
            ),
            array(
                'title' => 'Email address',
                'name' => 'emailAddress'
            ),
            array(
                'title' => 'Type',
                'name' => 'userType'
            ),
            array(
                'title' => 'Role',
                'name' => 'role'
            ),
            array(
                'title' => 'Last login',
                'name' => 'lastSuccessfulLoginDate',
                'formatter' => 'Date'
            ),
            array(
                'title' => '',
                'width' => 'checkbox',
                'data-field' => 'userId',
                'type' => 'CustomSelector',
            )
        );
    }
}
