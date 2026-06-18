<?php

namespace Dvsa\OlcsTest\Transfer\Util;

use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Transfer\Command\CommandContainerInterface;
use Laminas\InputFilter\InputFilterInterface;
use Mockery as m;

/**
 * Dto Test
 *
 * @NOTE this is a component test that tests the creation, filtering and validation of DTOs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DtoTest extends MockeryTestCase
{
    /**
     * @var AnnotationBuilder
     */
    protected $annotationBuilder;

    public function setUp(): void
    {
        $annotationBuilder = new AnnotationBuilder();

        $serviceManager = m::mock(ServiceManager::class);

        $annotationBuilder->setFilterManager(new FilterPluginManager($serviceManager));
        $annotationBuilder->setValidatorManager(new ValidatorPluginManager($serviceManager));

        $this->annotationBuilder = $annotationBuilder;
    }

    public function testCommand()
    {
        $data = [
            'structured' => [
                'id' => 1
            ]
        ];

        $dto = Stub\CommandDtoStub::create($data);

        /** @var CommandContainerInterface $command */
        $command = $this->annotationBuilder->createCommand($dto);

        // Test
        $this->assertInstanceOf(CommandContainerInterface::class, $command);

        // Test the CommandContainer
        $this->assertInstanceOf(InputFilterInterface::class, $command->getInputFilter());
        $this->assertSame($dto, $command->getDto());
        $this->assertEquals('test/route', $command->getRouteName());
        $this->assertEquals('POST', $command->getMethod());

        try {
            $command->getMessages();
            $ex = null;
        } catch (\Exception $ex) {
            // We are expecting to throw the exception all the time so we assert it later
        }

        // Assert the exception here, as we expect to always throw an exception
        $this->assertEquals('Validation has not yet occurred', $ex->getMessage());

        $this->assertFalse($command->isValid());

        $messages = $command->getMessages();

        $expectedMessages = [
            'id' => [
                'isEmpty' => 'Value is required and can\'t be empty'
            ],
            'list' => [
                'isEmpty' => 'Value is required and can\'t be empty'
            ],
            'structured' => [
                'version' => [
                    'isEmpty' => 'Value is required and can\'t be empty',
                ],
                'foo' => [
                    'isEmpty' => 'Value is required and can\'t be empty'
                ]
            ]
        ];

        $this->assertEquals($expectedMessages, $messages);
    }

    public function testCommandWithData()
    {
        $data = [
            'id' => 'abc123',
            'list' => [
                'duplicate   ',
                'duplicate',
                'unique',
                '',
                '   ',
                false,
                0
            ],
            'structured' => [
                'id' => 1,
                'version' => 1,
                'foo' => 'some un trimmed values       '
            ]
        ];

        $dto = Stub\CommandDtoStub::create($data);

        /** @var CommandContainerInterface $command */
        $command = $this->annotationBuilder->createCommand($dto);

        $this->assertTrue($command->isValid());

        $data = $dto->getArrayCopy();

        $expectedData = [
            'id' => 123,
            'list' => [
                0 => 'duplicate',
                2 => 'unique'
            ],
            'structured' => [
                'id' => 1,
                'version' => 1,
                'foo' => 'some un trimmed values'
            ],
            'imOptional' => null
        ];

        $this->assertEquals($expectedData, $data);
    }
}
