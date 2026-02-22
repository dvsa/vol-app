<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Parser;

use Dvsa\Olcs\Api\Service\Document\Parser\ParserFactory;

/**
 * Parser factory test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ParserFactoryTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('typeProvider')]
    public function testGetParser(mixed $type, mixed $class): void
    {
        $factory = new ParserFactory();
        $parser = $factory->getParser($type);

        $this->assertInstanceOf($class, $parser);
    }

    public function testGetParserWithUnknownType(): void
    {
        $factory = new ParserFactory();

        try {
            $parser = $factory->getParser('unknown');
        } catch (\RuntimeException $e) {
            $this->assertEquals('No parser found for mime type: unknown', $e->getMessage());
            return;
        }

        $this->fail('Expected exception not found');
    }

    public static function typeProvider(): array
    {
        return [
            ['application/rtf', \Dvsa\Olcs\Api\Service\Document\Parser\RtfParser::class],
            ['application/x-rtf', \Dvsa\Olcs\Api\Service\Document\Parser\RtfParser::class]
        ];
    }
}
