<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document;

use Dvsa\Olcs\Api\Service\Date as DateService;
use Dvsa\Olcs\Api\Service\Document\Document;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\DocumentShare\Service\DocumentStoreInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * @covers Dvsa\Olcs\Api\Service\Document\Document
 */
class DocumentTest extends TestCase
{
    /** @var DateService */
    private $dateService;

    /** @var DocumentStoreInterface */
    private $documentStore;

    /** @var TranslatorInterface */
    private $translator;

    /** @var Document */
    private $sut;

    public function setUp(): void
    {
        $this->dateService = m::mock(DateService::class);

        $this->documentStore = m::mock(DocumentStoreInterface::class);

        $this->translator = m::mock(TranslatorInterface::class);

        $this->sut = new Document(
            $this->dateService,
            $this->documentStore,
            $this->translator
        );
    }

    public function testGetBookmarkQueriesForNoBookmarks(): void
    {
        $file = new File();
        $file->setContent('');

        $queryData = $this->sut->getBookmarkQueries($file, []);
        $this->assertEquals([], $queryData);
    }

    public function testGetBookmarkQueriesForStaticBookmarks(): void
    {
        $content = <<<TXT
Bookmark 1: {\*\bkmkstart letter_date_add_14_days} {\*\bkmkend letter_date_add_14_days}.
Boomkark 2: {\*\bkmkstart todays_date}{\*\bkmkend todays_date}
TXT;
        $file = new File();
        $file->setContent($content);

        $queryData = $this->sut->getBookmarkQueries($file, []);
        $this->assertEquals([], $queryData);
    }

    public function testGetBookmarkQueriesForDynamicConcreteBookmarks(): void
    {
        $content = <<<TXT
Bookmark 1: {\*\bkmkstart caseworker_name} {\*\bkmkend caseworker_name}
Bookmark 2: {\*\bkmkstart licence_number} {\*\bkmkend licence_number}
TXT;
        $file = new File();
        $file->setContent($content);

        $queryData = $this->sut->getBookmarkQueries(
            $file,
            [
                'user' => 1,
                'licence' => 123
            ]
        );

        $this->assertArrayHasKey('caseworker_name', $queryData);
        $this->assertArrayHasKey('licence_number', $queryData);
    }

    public function testGetBookmarkQueriesForDynamicTextBlockBookmarks(): void
    {
        $content = <<<TXT
Bookmark 1: {\*\bkmkstart para_one} {\*\bkmkend para_one}
Bookmark 2: {\*\bkmkstart para_two} {\*\bkmkend para_two}
Bookmark 3: {\*\bkmkstart para_three} {\*\bkmkend para_three}
TXT;
        $file = new File();
        $file->setContent($content);

        $queryData = $this->sut->getBookmarkQueries(
            $file,
            [
                'bookmarks' => [
                    'para_one' => [1],
                    'para_three' => [2]
                ]
            ]
        );

        $this->assertArrayHasKey('para_one', $queryData);
        $this->assertArrayHasKey('para_three', $queryData);

        // we didn't supply any bookmark data for para two so we'd
        // expect it to not be in the query
        $this->assertArrayNotHasKey('para_two', $queryData);
    }

    public function testPopulateBookmarksWithStaticBookmarks(): void
    {
        $content = "Bookmark 1: {\*\bkmkstart todays_date} {\*\bkmkend todays_date}.";

        $file = new File();
        $file->setContent($content);

        $replaced = $this->sut->populateBookmarks(
            $file,
            []
        );

        // @NOTE: ideally we'd mock a todays_date bookmark instead of
        // using a real (and especially a date sensitive) one...
        $date = date("d/m/Y");

        $this->assertEquals(
            "Bookmark 1: " . $date . ".",
            $replaced
        );
    }

    public function testPopulateBookmarksWithDynamicBookmarks(): void
    {
        $content = "Bookmark 1: {\*\bkmkstart licence_number} {\*\bkmkend licence_number}.";

        $file = new File();
        $file->setContent($content);

        $replaced = $this->sut->populateBookmarks(
            $file,
            [
                'licence_number' => [
                    'licNo' => 1234
                ]
            ]
        );

        $this->assertEquals(
            "Bookmark 1: 1234.",
            $replaced
        );
    }

    public function testPopulateBookmarksWithDynamicBookmarksButNoData(): void
    {
        $content = "Bookmark 1: {\*\bkmkstart licence_number} {\*\bkmkend licence_number}.";

        $file = new File();
        $file->setContent($content);

        $replaced = $this->sut->populateBookmarks(
            $file,
            []
        );

        $this->assertEquals(
            $content,
            $replaced
        );
    }

    public function testPopulateBookmarksWithDynamicBookmarksImplementingDateAwareInterface(): void
    {
        $content = "Bookmark 1: {\*\bkmkstart Serial_Num} {\*\bkmkend Serial_Num}.";

        $file = new File();
        $file->setContent($content);

        $replaced = $this->sut->populateBookmarks(
            $file,
            []
        );
        $this->assertEquals(
            $content,
            $replaced
        );
    }

    public function testPopulateBookmarksWithDynamicBookmarksImplementingFileStoreAwareInterface(): void
    {
        $content = "Bookmark 1: {\*\bkmkstart TC_SIGNATURE} {\*\bkmkend TC_SIGNATURE}.";
        $file = new File();
        $file->setContent($content);

        $replaced = $this->sut->populateBookmarks(
            $file,
            []
        );
        $this->assertEquals(
            $content,
            $replaced
        );
    }

    public function testPopulateBookmarksWithDynamicBookmarksImplementingTranslatorAwareInterface(): void
    {
        $content = "Bookmark 1: {\*\bkmkstart User_Access} {\*\bkmkend User_Access}.";

        $file = new File();
        $file->setContent($content);

        $replaced = $this->sut->populateBookmarks(
            $file,
            []
        );
        $this->assertEquals(
            $content,
            $replaced
        );
    }
}
