<?php

namespace Dvsa\Olcs\Api\Service\Letter;

use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Service\Date as DateService;
use Dvsa\Olcs\Api\Service\Document\Bookmark\BookmarkFactory;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Interfaces\DateHelperAwareInterface;
use Dvsa\Olcs\Api\Service\Document\Parser\EditorJsParser;
use Dvsa\Olcs\Api\Domain\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Olcs\Logging\Log\Logger;

/**
 * VOL Grab Replacement Service
 *
 * Replaces [[placeholder]] tokens in EditorJS JSON content with database values
 * using the existing bookmark system.
 *
 */
class VolGrabReplacementService
{
    /**
     * Constructor
     *
     * @param BookmarkFactory       $bookmarkFactory Bookmark factory instance
     * @param QueryHandlerManager   $queryHandler    Query handler manager
     * @param DateService           $dateService     Date helper service
     * @param TranslatorInterface   $translator      Translator service
     */
    public function __construct(
        private readonly BookmarkFactory $bookmarkFactory,
        private readonly QueryHandlerManager $queryHandler,
        private readonly DateService $dateService,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * Replace VOL grabs in EditorJS JSON content
     *
     * @param string $editorJsJson EditorJS JSON content as string
     * @param array  $context      Entity context (licence, application, user, etc.)
     *
     * @return string Updated EditorJS JSON content with placeholders replaced
     */
    public function replaceGrabs(string $editorJsJson, array $context): string
    {
        if (empty($editorJsJson)) {
            return $editorJsJson;
        }

        try {
            // Create parser instance
            $parser = new EditorJsParser();

            // Step 1: Extract tokens from JSON
            $tokens = $parser->extractTokens($editorJsJson);

            if (empty($tokens)) {
                return $editorJsJson; // No placeholders found
            }

            // Step 2: Get bookmark instances (tokens passed directly to BookmarkFactory)
            $bookmarks = $this->getBookmarks($tokens, $parser);

            // Step 3: Execute queries for dynamic bookmarks
            $queryResults = $this->executeQueries($bookmarks, $context);

            // Step 4: Render bookmarks to get values
            $populatedData = $this->renderBookmarks($bookmarks, $queryResults);

            // Step 5: Replace in JSON
            return $parser->replace($editorJsJson, $populatedData);
        } catch (\Exception $e) {
            // Log error but return original content to avoid breaking letters
            Logger::err('VOL Grab replacement failed: ' . $e->getMessage());
            return $editorJsJson;
        }
    }

    /**
     * Get bookmark instances from factory
     *
     * Tokens are passed directly to BookmarkFactory which converts them to class names
     * using the same convention as RTF bookmarks (e.g., OP_NAME → OpName).
     *
     * @param array           $tokens Array of token names from JSON
     * @param EditorJsParser  $parser Parser instance to inject into bookmarks
     *
     * @return array Array of bookmark instances keyed by token
     */
    private function getBookmarks(array $tokens, EditorJsParser $parser): array
    {
        $bookmarks = [];

        foreach ($tokens as $token) {
            try {
                // BookmarkFactory automatically converts token to class name
                // e.g., OP_NAME → OpName, CASEWORKER_NAME → CaseworkerName
                $bookmark = $this->bookmarkFactory->locate($token);

                // Inject dependencies
                $bookmark->setParser($parser);

                if ($bookmark instanceof DateHelperAwareInterface) {
                    $bookmark->setDateHelper($this->dateService);
                }

                if ($bookmark instanceof TranslatorAwareInterface) {
                    $bookmark->setTranslator($this->translator);
                }

                // Note: FileStoreAwareInterface not injected - not needed for letters

                $bookmarks[$token] = $bookmark;
            } catch (\Exception $e) {
                // Skip bookmarks that can't be created
                // This allows graceful handling of unknown/invalid placeholders
                Logger::warn(sprintf(
                    'Failed to create bookmark for token "%s": %s',
                    $token,
                    $e->getMessage()
                ));
            }
        }

        return $bookmarks;
    }

    /**
     * Execute queries for dynamic bookmarks
     *
     * @param array $bookmarks Bookmark instances
     * @param array $context   Entity context for queries
     *
     * @return array Query results keyed by token
     */
    private function executeQueries(array $bookmarks, array $context): array
    {
        $results = [];

        foreach ($bookmarks as $token => $bookmark) {
            // Skip static bookmarks (no query needed)
            if ($bookmark->isStatic()) {
                continue;
            }

            try {
                $query = $bookmark->validateDataAndGetQuery($context);

                if ($query !== null) {
                    // Handle single query
                    if (!is_array($query)) {
                        $results[$token] = $this->queryHandler->handleQuery($query);
                    } else {
                        // Handle array of queries (rare case like TextBlock)
                        $list = [];
                        foreach ($query as $qry) {
                            $list[] = $this->queryHandler->handleQuery($qry);
                        }
                        $results[$token] = $list;
                    }
                }
            } catch (\Exception $e) {
                // Log query errors but continue processing other bookmarks
                Logger::err(sprintf(
                    'Query failed for bookmark token "%s": %s',
                    $token,
                    $e->getMessage()
                ));
            }
        }

        return $results;
    }

    /**
     * Render bookmarks to get string values
     *
     * @param array $bookmarks    Bookmark instances
     * @param array $queryResults Query results for dynamic bookmarks
     *
     * @return array Rendered values keyed by token
     */
    private function renderBookmarks(array $bookmarks, array $queryResults): array
    {
        $populated = [];

        foreach ($bookmarks as $token => $bookmark) {
            try {
                if ($bookmark->isStatic()) {
                    // Static bookmark - just render
                    $result = $bookmark->render();
                } elseif (isset($queryResults[$token])) {
                    // Dynamic bookmark - set data and render
                    $bookmark->setData($queryResults[$token]);
                    $result = $bookmark->render();
                } else {
                    // No data available for this bookmark
                    $result = null;
                }

                if ($result !== null) {
                    // Note: No RTF encoding needed for EditorJS!
                    // The parser will handle newline conversion to <br>
                    $populated[$token] = [
                        'content' => $result,
                        'preformatted' => $bookmark->isPreformatted()
                    ];
                }
            } catch (\Exception $e) {
                // Log render errors but continue processing other bookmarks
                Logger::warn(sprintf(
                    'Render failed for bookmark token "%s": %s',
                    $token,
                    $e->getMessage()
                ));
            }
        }

        return $populated;
    }
}
