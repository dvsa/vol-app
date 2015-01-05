<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class TaskReassignTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class TaskReassignTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\Task';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['assignment', 'assignedToTeam'],
                ['t1' => 'Team 1', 't2' => 'Team2']
            ],
            [
                ['assignment', 'assignedToUser'],
                ['u1' => 'User 1', 'u2' => 'User 2']
            ]
        ];
    }

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['assignment', 'assignedToTeam']),
                new F\Value(F\Value::VALID, 't1'),
                new F\Value(F\Value::VALID, 't2'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['assignment', 'assignedToUser']),
                new F\Value(F\Value::VALID, 'u1'),
                new F\Value(F\Value::VALID, 'u2'),
                new F\Value(F\Value::INVALID, null)
            ),
        ];
    }
}
