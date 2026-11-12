<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Letter\LetterInstanceIssue;

use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceIssue\UpdateContent;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * An Other issue's heading is plain text the caseworker writes (VOL-7735). It's escaped wherever
 * it's shown, so it isn't tag-stripped on the way in: that cut "Fewer than <5 vehicles" short.
 */
final class UpdateContentTest extends MockeryTestCase
{
    public static function headings(): \Iterator
    {
        yield 'less than' => ['Fewer than <5 vehicles authorised'];
        yield 'greater than' => ['Vehicles > 3.5 tonnes'];
        yield 'looks like a tag' => ['Use of <b> on the disc'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('headings')]
    public function testTheHeadingIsKeptAsWritten(string $heading): void
    {
        $command = UpdateContent::create([
            'id' => 1,
            'version' => 1,
            'editedContent' => '{"blocks":[]}',
            'editedHeading' => '  ' . $heading . ' ',
        ]);

        $annotationBuilder = new AnnotationBuilder();
        $annotationBuilder->setFilterManager(new FilterPluginManager(m::mock(ServiceManager::class)));
        $annotationBuilder->setValidatorManager(new ValidatorPluginManager(m::mock(ServiceManager::class)));
        $container = $annotationBuilder->createCommand($command);

        $this->assertTrue($container->isValid());
        $this->assertSame($heading, $command->getEditedHeading(), 'trimmed, otherwise as written');
    }
}
