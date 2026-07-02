<?php

namespace CommonTest\FormService;

use Common\FormService\FormServiceManager;
use Common\FormService\FormServiceManagerFactory;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;

class FormServiceManagerFactoryTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    protected $sm;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new FormServiceManagerFactory();
        $this->sm = new ServiceManager();
    }

    public function testInvoke(): void
    {
        // Params
        $config = [
            'form_service_manager' => [
                'invokables' => [
                    'foo' => '\stdClass'
                ]
            ]
        ];

        // Mocks
        $this->sm->setService('Config', $config);

        $brm = $this->sut->__invoke($this->sm, FormServiceManager::class);

        $this->assertInstanceOf(FormServiceManager::class, $brm);
        $this->assertTrue($brm->has('foo'));
        $this->assertFalse($brm->has('bar'));
    }
}
