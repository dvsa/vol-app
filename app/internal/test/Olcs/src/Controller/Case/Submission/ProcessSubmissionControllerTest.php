<?php

/**
 * ProcessSubmissionController Test Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Controller\ControllerTestAbstract;
use Mockery as m;

/**
 * ConditionUndertaking Test Controller
 */
class ProcessSubmissionControllerTest extends ControllerTestAbstract
{
    protected $testClass = 'Olcs\Controller\Cases\Submission\ProcessSubmissionController';

    public function testAssignAction()
    {
        $this->markTestSkipped('Logger service not found to be fixed');

        $class = m::mock($this->testClass);
        $class->shouldReceive('editAction');
    }
}
