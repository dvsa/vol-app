<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process;

/**
 * Class AbstractText
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
abstract class AbstractText implements ProcessInterface
{
    private $text = [];

    /**
     * Add a line of text
     *
     * @param string $text text
     *
     * @return void
     */
    protected function addTextLine($text)
    {
        if ($text) {
            $this->text[] = $text;
        }
    }

    /**
     * Get all the text seperated with \n new lines
     *
     * @return string
     */
    protected function getTextWithNewLine()
    {
        return implode("\n", $this->text);
    }

    /**
     * Clear existed text
     *
     * @return void
     */
    protected function clear()
    {
        $this->text = [];
    }
}
