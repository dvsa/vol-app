<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\TransportManagerApplication;

use Dvsa\Olcs\Transfer\Command\Tm;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DateOfBirthValidationTest extends TestCase
{
    #[DataProvider('commands')]
    public function testTransportManagerWriteCommandsValidateDateOfBirth(string $commandClass, string $field): void
    {
        $builder = new AnnotationBuilder();
        $serviceManager = new ServiceManager();
        $builder->setFilterManager(new FilterPluginManager($serviceManager));
        $builder->setValidatorManager(new ValidatorPluginManager($serviceManager));
        $container = $builder->createCommand($commandClass::create([]));
        $input = $container->getInputFilter()->get($field);

        $input->setValue('2099-01-01');
        self::assertFalse($input->isValid());
        self::assertArrayHasKey('inFuture', $input->getMessages());

        $input->setValue('not-a-date');
        self::assertFalse($input->isValid());
        self::assertArrayHasKey('dateInvalidDate', $input->getMessages());

        $input->setValue('1990-01-01');
        self::assertTrue($input->isValid());
    }

    public static function commands(): array
    {
        return [
            'application details' => [TransportManagerApplication\UpdateDetails::class, 'dob'],
            'application create' => [TransportManagerApplication\Create::class, 'dob'],
            'new user' => [Tm\CreateNewUser::class, 'birthDate'],
            'internal create' => [Tm\Create::class, 'birthDate'],
            'internal update' => [Tm\Update::class, 'birthDate'],
        ];
    }
}
