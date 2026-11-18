<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\TranslationKey;

use Dvsa\Olcs\Transfer\Command\TranslationKey\Update;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;

/**
 * Update test
 */
final class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 'STRID',
            'translationsArray' => [
                'en_GB' => 'English',
                'cy_GB' => 'Welsh',
                'en_NI' => 'English (NI)',
                'cy_NI' => 'Welsh (NI)'
            ]
        ];

        $command = Update::create($data);

        $this->assertEquals($data['id'], $command->getId());
        $this->assertEquals($data['translationsArray'], $command->getTranslationsArray());
    }

    public function testDescriptionAllowsUpTo512Characters(): void
    {
        $serviceManager = new ServiceManager();
        $builder = new AnnotationBuilder();
        $builder->setFilterManager(new FilterPluginManager($serviceManager));
        $builder->setValidatorManager(new ValidatorPluginManager($serviceManager));

        $command = Update::create(['id' => 1, 'translationsArray' => ['en_GB' => 'abc'], 'description' => str_repeat('a', 512)]);

        $container = $builder->createCommand($command);

        $this->assertTrue($container->isValid(), json_encode($container->getMessages()));
    }
}
