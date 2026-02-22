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
     * @param BookmarkFactory       $bookmarkFactory Factory for creating bookmark instances from token names
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
     * Regex pattern to match [[TOKEN_NAME]] placeholders
     * Only matches uppercase letters, numbers, and underscores (bookmark token format)
     */
    private const string GRAB_PATTERN = '/\[\[([A-Z0-9_]+)\]\]/';

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
     * @param array                $tokens Array of token names from JSON/HTML
     * @param EditorJsParser|null  $parser Parser instance to inject into bookmarks (null for HTML processing)
     *
     * @return array Array of bookmark instances keyed by token
     */
    private function getBookmarks(array $tokens, ?EditorJsParser $parser = null): array
    {
        $bookmarks = [];

        foreach ($tokens as $token) {
            try {
                // BookmarkFactory automatically converts token to class name
                // e.g., OP_NAME → OpName, CASEWORKER_NAME → CaseworkerName
                $bookmark = $this->bookmarkFactory->locate($token);

                // Inject parser only for EditorJS processing
                if ($parser !== null) {
                    $bookmark->setParser($parser);
                }

                if ($bookmark instanceof DateHelperAwareInterface) {
                    $bookmark->setDateHelper($this->dateService);
                }

                if ($bookmark instanceof TranslatorAwareInterface) {
                    $bookmark->setTranslator($this->translator);
                }

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
     * @param bool  $forHtml      True for HTML output (simple strings), false for EditorJS (structured data)
     *
     * @return array Rendered values keyed by token
     */
    private function renderBookmarks(array $bookmarks, array $queryResults, bool $forHtml = false): array
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
                    if ($forHtml) {
                        $populated[$token] = nl2br($result, false);
                    } else {
                        // EditorJS: Return structured data for parser
                        $populated[$token] = [
                            'content' => $result,
                            'preformatted' => $bookmark->isPreformatted()
                        ];
                    }
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

    /**
     * Replace VOL grabs in HTML content
     *
     * Unlike replaceGrabs() which handles EditorJS JSON, this method works on
     * plain HTML/text content directly. Used for master template processing.
     *
     * @param string $html    HTML content containing [[TOKEN]] placeholders
     * @param array  $context Entity context (licence, application, user, etc.)
     *
     * @return string HTML content with placeholders replaced
     */
    public function replaceGrabsInHtml(string $html, array $context): string
    {
        if (empty($html)) {
            return $html;
        }

        try {
            $tokens = $this->extractTokensFromHtml($html);

            if (empty($tokens)) {
                return $html;
            }

            // Use shared methods with no parser (null) and forHtml=true
            $bookmarks = $this->getBookmarks($tokens, null);
            $queryResults = $this->executeQueries($bookmarks, $context);
            $populatedData = $this->renderBookmarks($bookmarks, $queryResults, true);

            return $this->replaceTokensInHtml($html, $populatedData);
        } catch (\Exception $e) {
            // Log error but return original content to avoid breaking letters
            Logger::err('VOL Grab replacement in HTML failed: ' . $e->getMessage());
            return $html;
        }
    }

    /**
     * Extract [[TOKEN]] placeholders from HTML content
     *
     * @param string $html HTML content
     *
     * @return array Array of unique token names found
     */
    private function extractTokensFromHtml(string $html): array
    {
        if (preg_match_all(self::GRAB_PATTERN, $html, $matches)) {
            return array_unique($matches[1]);
        }

        return [];
    }

    /**
     * Replace tokens in HTML content with their values
     *
     * @param string $html HTML content
     * @param array  $data Associative array of token => value
     *
     * @return string HTML with tokens replaced
     */
    private function replaceTokensInHtml(string $html, array $data): string
    {
        foreach ($data as $token => $value) {
            $placeholder = '[[' . $token . ']]';
            $html = str_replace($placeholder, (string)$value, $html);
        }

        return $html;
    }
}
