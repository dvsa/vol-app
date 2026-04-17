<?php

namespace Dvsa\Olcs\Api\Service\Document\Parser;

/**
 * RTF parser
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class RtfParser implements ParserInterface
{
    public const PIXELS_TO_TWIPS = 15;

    /**
     * Returns the file extension (rtf)
     *
     * @return string
     */
    #[\Override]
    public function getFileExtension()
    {
        return 'rtf';
    }

    /**
     * Extracts tokens
     *
     * @param string $content content to extract from
     *
     * @return array
     */
    #[\Override]
    public function extractTokens($content)
    {
        $matches = $this->getMatches($content);
        $tokens = [];
        for ($i = 0; $i < count($matches[0]); $i++) {
            $tokens[] = $matches[1][$i];
        }
        return $tokens;
    }

    /**
     * Replace bookmarks within the data
     *
     * @param string $content snippet containing bookmarks
     * @param array  $data    data for the bookmarks
     *
     * @return mixed
     */
    #[\Override]
    public function replace($content, $data)
    {
        $matches = $this->getMatches($content);

        $search  = [];
        $replace = [];

        for ($i = 0; $i < count($matches[0]); $i++) {
            $literal = $matches[0][$i];
            $token   = $matches[1][$i];
            // bear in mind the later str_replace will replace *all*
            // bookmarks of this name; probably what we want of course,
            // but worth being clear about
            if (isset($data[$token])) {
                $current = $data[$token];
                if (is_array($current)) {
                    $str = $current['content'];
                    $formatted = $current['preformatted'];
                } else {
                    $str = $current;
                    $formatted = false;
                }
                $search[]  = $literal;
                // we assume each bookmark will return 'plain' text, so
                // replace certain characters with RTF style markup
                $replace[] = $this->format($str, $formatted);
            }
        }

        return str_replace($search, $replace, $content);
    }

    /**
     * Render an image
     *
     * @param string $binData binary image data
     * @param int    $width   image width
     * @param int    $height  image height
     * @param string $type    image type
     *
     * @return string
     */
    #[\Override]
    public function renderImage($binData, $width, $height, $type)
    {
        // VOL-7060: Gotenberg's LibreOffice drops a template's existing
        // `\*\picprop` shape-pictures (e.g. the OTC / NI office logos
        // embedded in GB and NI licence templates) whenever the document
        // later contains a bare `\pict` group. Word authors image data as
        // a shape via `\*\shppict{\*\picprop\shplidN…}`; emitting the same
        // form, with a `\shplid` that cannot collide with a template's
        // existing shapes (Word templates start at 1025 and count up), is
        // enough to stop LibreOffice conflating the injected picture with
        // an earlier shape. The id is derived from the image bytes so the
        // output stays deterministic across calls and across test runs.
        $shplid = 0x20000 + (crc32($binData) & 0xFFFF);

        return sprintf(
            '{\*\shppict{\pict{\*\picprop\shplid%d'
            . '{\sp{\sn shapeType}{\sv 75}}'
            . '{\sp{\sn fLockAspectRatio}{\sv 1}}'
            . '{\sp{\sn fLayoutInCell}{\sv 1}}}'
            . '\picscalex100\picscaley100\piccropl0\piccropr0\piccropt0\piccropb0'
            . '\picw%d\pich%d\picwgoal%d\pichgoal%d'
            . '\%sblip %s}}',
            $shplid,
            $width,
            $height,
            $width * self::PIXELS_TO_TWIPS,
            $height * self::PIXELS_TO_TWIPS,
            $type,
            bin2hex($binData)
        );
    }

    /**
     * Find bookmarks within the data
     *
     * @param string $content data being searched
     *
     * @return array
     */
    private function getMatches($content)
    {
        preg_match_all(
            "#{\\\.\\\bkmkstart\s([^}]+)}[^{]*{\\\.\\\bkmkend\s[^}]+}#",
            $content,
            $matches
        );
        return $matches;
    }

    /**
     * format the string
     *
     * @param string $data      data to be formatted
     * @param bool   $formatted whether the data has already been formatted
     *
     * @return mixed
     */
    private function format($data, $formatted = false)
    {
        if ($formatted) {
            return $data;
        }

        return str_replace("\n", "\par ", $data);
    }

    /**
     * Helper method to replace special characters (e.g. apostrophes) with entities,
     * and to quote reserved RTF characters (e.g. { and })
     *
     * @param string $data data to be modified
     *
     * @return string
     */
    public function getEntitiesAndQuote($data)
    {
        return \PHPRtfLite_Utf8::getUnicodeEntities(\PHPRtfLite::quoteRtfCode($data, true), 'UTF-8');
    }
}
