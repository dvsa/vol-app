<?php

namespace Dvsa\Olcs\Api\Service\Document\Parser;

/**
 * Parser factory class
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ParserFactory
{
    public function getParser($mime)
    {
        return match ($mime) {
            'text/rtf', 'application/rtf', 'application/x-rtf' => new RtfParser(),
            'application/json', 'text/json' => new EditorJsParser(),
            default => throw new \RuntimeException('No parser found for mime type: ' . $mime),
        };
    }
}
