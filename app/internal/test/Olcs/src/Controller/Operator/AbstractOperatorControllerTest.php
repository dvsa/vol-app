<?php

/**
 * Abstract operator controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Operator;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Abstract operator controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class AbstractOperatorControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var array
     */
    protected $mockMethods = [];

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );

        $this->controller = $this->getMock($this->controllerName, $this->mockMethods);
    }
}
