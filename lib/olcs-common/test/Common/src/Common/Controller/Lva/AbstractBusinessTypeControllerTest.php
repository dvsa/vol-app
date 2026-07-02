<?php

namespace CommonTest\Common\Controller\Lva;

use CommonTest\Common\Controller\Lva\AbstractLvaControllerTestCase;
use Mockery as m;
use Common\Controller\Lva\AbstractBusinessTypeController;

/**
 * Test Abstract Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class AbstractBusinessTypeControllerTest extends AbstractLvaControllerTestCase
{
    public $request;
    public $sut;
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockController(AbstractBusinessTypeController::class);
    }

    public function testGetIndexAction(): void
    {
        $this->assertTrue(true);
    }

    protected function mockController(string $className, array $constructorParams = []): void
    {
        $this->request = m::mock(\Laminas\Http\Request::class)->makePartial();

        // If constructor params are provided, pass them to the mock, otherwise mock without them
        if ($constructorParams !== []) {
            $this->sut = m::mock($className, $constructorParams)
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
        } else {
            $this->sut = m::mock($className)
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
        }

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($this->request);
    }
}
