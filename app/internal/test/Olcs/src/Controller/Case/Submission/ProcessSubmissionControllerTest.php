<?php

namespace OlcsTest\Controller\Submission;

use OlcsTest\Controller\ControllerTestAbstract;
use Mockery as m;

/**
 * ConditionUndertaking Test Controller
 * @covers Olcs\Controller\Cases\Submission\ProcessSubmissionController
 */
class ProcessSubmissionControllerTest extends ControllerTestAbstract
{
    protected $testClass = 'Olcs\Controller\Cases\Submission\ProcessSubmissionController';

    public function testAssignAction()
    {
        $class = m::mock($this->testClass);
        $class->shouldReceive('editAction');
    }
}
