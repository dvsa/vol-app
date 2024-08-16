<?php

namespace Dvsa\Olcs\Api\Service\Document\Parser;

/**
 * Parser interface
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
interface ParserInterface
{
    public function extractTokens($content);

    public function replace($content, $data);

    public function getFileExtension();

    public function renderImage($binData, $width, $height, $type);
}
