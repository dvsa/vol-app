<?php

namespace Dvsa\Olcs\Api\Service\Document;

use Dvsa\Olcs\Api\Domain\TranslatorAwareInterface;
use Dvsa\Olcs\Api\Service\Date as DateService;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Interfaces\DateHelperAwareInterface;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Interfaces\FileStoreAwareInterface;
use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\Olcs\DocumentShare\Service\DocumentStoreInterface;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Document generation service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class Document
{
    public const DOCUMENT_TIMESTAMP_FORMAT = 'YmdHi';

    /**
     * Create service instance
     *
     *
     * @return Document
     */
    public function __construct(private readonly DateService $dateSrvHlpr, private readonly DocumentStoreInterface $documentStore, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Get Bookmark Queries
     *
     * @param ContentStoreFile $file File object
     * @param array            $data Query data
     *
     * @return array
     */
    public function getBookmarkQueries(ContentStoreFile $file, $data)
    {
        $queryData = [];

        /**
         * @NOTE We no longer store MIME Type, at the moment it is safe to assum RTF, however this could change
         */
        $tokens = $this->getParser('application/rtf')
            ->extractTokens($file->getContent());

        $bookmarks = $this->getBookmarks($tokens);
        foreach ($bookmarks as $token => $bookmark) {
            // we don't need to query if the bookmark is static (i.e.
            // doesn't rely on any backend information)
            if ($bookmark->isStatic()) {
                continue;
            }

            $query = $bookmark->validateDataAndGetQuery($data);

            // we need to allow for the fact the bookmark might not want
            // to actually generate a query in which case it can return
            // a null value
            if ($query !== null) {
                $queryData[$token] = $query;
            }
        }

        return $queryData;
    }

    /**
     * Populate Bookmarks
     *
     * @param ContentStoreFile $file File object
     * @param array            $data Data
     *
     * @return mixed
     */
    public function populateBookmarks(ContentStoreFile $file, $data)
    {
        $populatedData = [];

        $content = $file->getContent();

        /**
         * @NOTE We no longer store MIME Type, at the moment it is safe to assum RTF, however this could change
         */
        $parser = $this->getParser('application/rtf');
        $tokens = $parser->extractTokens($content);

        $bookmarks = $this->getBookmarks($tokens);

        foreach ($bookmarks as $token => $bookmark) {
            /**
             * Let the bookmark know what parser is currently active;
             * some may use this for sub-bookmark processing
             */
            $bookmark->setParser($parser);

            if ($bookmark->isStatic()) {
                $result = $bookmark->render();
            } elseif (isset($data[$token])) {
                $bookmark->setData($data[$token]);
                $result = $bookmark->render();
            } else {
                // no data to fulfil this dynamic bookmark, but that's okay
                $result = null;
            }
            // this check means bookmarks we did find but couldn't replace with
            // data are left in tact in the document, which can appear confusing. We
            // do this for now because of course *every* token has a bookmark, even if
            // it's a fallback TextBlock. Could modify the below to check the bookmark type...
            if ($result !== null) {
                // convert any extended chars to RTF versions
                $result = \PHPRtfLite_Utf8::getUnicodeEntities((string)$result, 'UTF-8');

                $populatedData[$token] = [
                    'content' => $result,
                    'preformatted' => $bookmark->isPreformatted()
                ];
            }
        }

        return $parser->replace($content, $populatedData);
    }

    /**
     * Returns Parser object
     *
     * @param string $type Parser Mime type
     *
     * @return Parser\RtfParser
     */
    private function getParser($type)
    {
        $factory = new Parser\ParserFactory();
        return $factory->getParser($type);
    }

    /**
     * Get Bookmarks
     *
     * @param array $tokens Tokens
     *
     * @return array
     */
    private function getBookmarks($tokens)
    {
        $bookmarks = [];

        $factory = new Bookmark\BookmarkFactory();
        foreach ($tokens as $token) {
            $bookmark = $factory->locate($token);

            if ($bookmark instanceof DateHelperAwareInterface) {
                $bookmark->setDateHelper($this->dateSrvHlpr);
            }

            if ($bookmark instanceof FileStoreAwareInterface) {
                $bookmark->setFileStore($this->documentStore);
            }

            if ($bookmark instanceof TranslatorAwareInterface) {
                $bookmark->setTranslator($this->translator);
            }

            $bookmarks[$token] = $bookmark;
        }

        return $bookmarks;
    }
}
