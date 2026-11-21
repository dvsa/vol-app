<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Messages;

use Common\Form\Form;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\Olcs\Transfer\Command\Messaging\Message\Create as CreateMessageCommand;
use Dvsa\Olcs\Transfer\Query\Messaging\Documents;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Form\Element\Hidden;
use Laminas\Form\ElementInterface;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Navigation\Navigation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Messages\LicenceConversationMessagesController as Sut;
use Olcs\Mvc\Controller\Plugin\Placeholder;

final class LicenceConversationMessagesControllerTest extends MockeryTestCase
{
    private const string CORRELATION_ID = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    private m\MockInterface $flashMessenger;
    private m\MockInterface $placeholder;
    private Hidden $correlationIdOnForm;

    private function sut(array $post): Sut
    {
        $this->flashMessenger = m::mock(FlashMessengerHelperService::class);

        $sut = m::mock(Sut::class, [
            m::mock(TranslationHelperService::class),
            m::mock(FormHelperService::class),
            $this->flashMessenger,
            m::mock(Navigation::class),
            m::mock(ScriptFactory::class),
            m::mock(FileUploadHelperService::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $params = m::mock(Params::class);
        $params->allows('fromPost')->withNoArgs()->andReturn($post);
        $params->allows('fromPost')->with('correlationId')->andReturn($post['correlationId'] ?? null);
        $params->allows('fromPost')->with('action')->andReturn($post['action'] ?? null);
        $params->allows('fromRoute')->with('conversation')->andReturn('55');
        $params->allows('fromRoute')->withNoArgs()->andReturn(['licence' => '7', 'conversation' => '55']);
        $sut->allows('params')->andReturn($params);

        $this->placeholder = m::mock(Placeholder::class);
        $sut->allows('placeholder')->andReturn($this->placeholder);

        return $sut;
    }

    private function form(bool $valid, string $reply = ''): Form
    {
        $this->correlationIdOnForm = new Hidden('correlationId');

        $replyElement = m::mock(ElementInterface::class);
        $replyElement->allows('getValue')->andReturn($reply);

        $form = m::mock(Form::class);
        $form->allows('setData');
        $form->allows('get')->with('id')->andReturn(new Hidden('id'));
        $form->allows('get')->with('correlationId')->andReturn($this->correlationIdOnForm);
        $form->allows('get')->with('form-actions')->andReturn(
            m::mock(ElementInterface::class)->allows('get')->with('reply')->andReturn($replyElement)->getMock()
        );
        $form->allows('isValid')->andReturn($valid);

        return $form;
    }

    private function parseReply(Sut $sut, Form $form, bool $canReply = true): mixed
    {
        return (new \ReflectionMethod(Sut::class, 'parseReply'))->invoke($sut, $form, $canReply);
    }

    private function handlePost(Sut $sut, Form $form): mixed
    {
        return (new \ReflectionMethod(Sut::class, 'handlePost'))->invoke($sut, $form, true);
    }

    private function expectTheConversationToBeShownWithTheReplyOpen(Sut $sut): void
    {
        $sut->expects('handleCommand')->never();
        $this->placeholder->expects('setPlaceholder')->with('open-reply', true);
        $sut->expects('renderConversation')->andReturn('VIEW');
    }

    private function uploadCommandFor(string $fileName): Upload
    {
        $sut = $this->sut(['action' => 'reply', 'correlationId' => self::CORRELATION_ID]);

        $response = m::mock(Response::class);
        $response->allows('isClientError')->andReturn(false);
        $response->allows('isOk')->andReturn(true);
        $sent = [];
        $sut->allows('handleCommand')->andReturnUsing(function (Upload $command) use (&$sent, $response) {
            $sent[] = $command;

            return $response;
        });

        $tmp = tempnam(sys_get_temp_dir(), 'vol7224');
        try {
            file_put_contents($tmp, 'content');
            $sut->processFileUpload(['name' => $fileName, 'tmp_name' => $tmp, 'type' => 'application/pdf']);
        } finally {
            unlink($tmp);
        }

        $this->assertCount(1, $sent);

        return $sent[0];
    }

    public function testChoosingAFileStoresItAndDoesNotSend(): void
    {
        $sut = $this->sut([
            'correlationId' => self::CORRELATION_ID,
            'form-actions' => ['file' => ['upload' => 'Upload']],
        ]);
        $form = $this->form(true, 'A valid reply');

        $sut->expects('processFiles')
            ->with($form, 'form-actions->file', m::type('callable'), m::type('callable'), m::type('callable'))
            ->andReturn(true);
        $this->expectTheConversationToBeShownWithTheReplyOpen($sut);

        $this->assertSame('VIEW', $this->handlePost($sut, $form));
        $this->assertSame(self::CORRELATION_ID, $this->correlationIdOnForm->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('uploadOrRemovePostProvider')]
    public function testAnUploadOrRemoveNeverSendsTheReplyWhateverHappenedToTheFile(array $filePost): void
    {
        $sut = $this->sut([
            'action' => 'reply',
            'correlationId' => self::CORRELATION_ID,
            'form-actions' => ['reply' => 'A valid reply', 'file' => $filePost],
        ]);
        $form = $this->form(true, 'A valid reply');

        $sut->expects('processFiles')->andReturn(false);
        $this->expectTheConversationToBeShownWithTheReplyOpen($sut);

        $this->assertSame('VIEW', $this->parseReply($sut, $form));
    }

    public static function uploadOrRemovePostProvider(): \Iterator
    {
        yield 'upload sent by the page script' => [['upload' => 'Upload']];
        yield 'upload button pressed with the page script off' => [['upload' => '']];
        yield 'remove' => [['list' => ['file-9' => ['id' => '9', 'version' => '1', 'remove' => 'Remove']]]];
    }

    public function testSendWithFilesListedSendsTheReplyWithTheCorrelationId(): void
    {
        $sut = $this->sut([
            'id' => '55',
            'correlationId' => self::CORRELATION_ID,
            'action' => 'reply',
            'form-actions' => [
                'reply' => 'A valid reply',
                'file' => [
                    'fileCount' => '',
                    '__messages__' => '',
                    'list' => ['file-9' => ['id' => '9', 'version' => '1']],
                ],
                'actions' => ['send' => ''],
            ],
            'security' => 'token',
        ]);
        $form = $this->form(true, 'A valid reply');

        $sut->expects('processFiles')->andReturn(false);

        $response = m::mock(Response::class);
        $response->allows('isOk')->andReturn(true);
        $sut->expects('handleCommand')
            ->with(m::on(function (CreateMessageCommand $command): bool {
                $this->assertSame(self::CORRELATION_ID, $command->getCorrelationId());
                $this->assertSame('A valid reply', $command->getMessageContent());
                $this->assertSame(55, (int) $command->getConversation());

                return true;
            }))
            ->andReturn($response);

        $this->flashMessenger->expects('addSuccessMessage')->with('Reply submitted successfully');
        $redirect = new HttpResponse();
        $sut->expects('redirect->toRoute')
            ->with('licence/conversation/view', ['licence' => '7', 'conversation' => '55'])
            ->andReturn($redirect);

        $this->assertSame($redirect, $this->parseReply($sut, $form));
    }

    public function testInvalidReplyIsNotSent(): void
    {
        $sut = $this->sut(['action' => 'reply', 'correlationId' => self::CORRELATION_ID]);
        $form = $this->form(false, 'abc');

        $sut->expects('processFiles')->andReturn(false);
        $this->expectTheConversationToBeShownWithTheReplyOpen($sut);

        $this->assertSame('VIEW', $this->parseReply($sut, $form));
    }

    public function testPostWithoutAnActionNeverSends(): void
    {
        $sut = $this->sut(['correlationId' => self::CORRELATION_ID]);
        $form = $this->form(true, 'A valid reply');

        $sut->expects('processFiles')->andReturn(false);
        $this->expectTheConversationToBeShownWithTheReplyOpen($sut);

        $this->assertSame('VIEW', $this->handlePost($sut, $form));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('noUsableCorrelationIdProvider')]
    public function testReplyFromAPageWithoutACorrelationIdStillSends(array $post): void
    {
        $sut = $this->sut(['action' => 'reply'] + $post);
        $form = $this->form(true, 'A valid reply');

        $sut->expects('processFiles')->never();

        $response = m::mock(Response::class);
        $response->allows('isOk')->andReturn(true);
        $sut->expects('handleCommand')
            ->with(m::on(fn (CreateMessageCommand $command): bool => $command->getCorrelationId() === null
                && $command->getMessageContent() === 'A valid reply'))
            ->andReturn($response);

        $this->flashMessenger->expects('addSuccessMessage')->with('Reply submitted successfully');
        $redirect = new HttpResponse();
        $sut->expects('redirect->toRoute')->andReturn($redirect);

        $this->assertSame($redirect, $this->parseReply($sut, $form));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{40}$/', $this->correlationIdOnForm->getValue());
    }

    public static function noUsableCorrelationIdProvider(): \Iterator
    {
        yield 'field missing' => [[]];
        yield 'field empty' => [['correlationId' => '']];
        yield 'field is not text' => [['correlationId' => ['a']]];
    }

    public function testUploadToAConversationThatHasBeenClosedIsNotStored(): void
    {
        $sut = $this->sut([
            'correlationId' => self::CORRELATION_ID,
            'form-actions' => ['file' => ['upload' => 'Upload']],
        ]);
        $form = $this->form(true);

        $sut->expects('processFiles')->never();
        $this->expectTheConversationToBeShownWithTheReplyOpen($sut);

        $this->assertSame('VIEW', $this->parseReply($sut, $form, false));
    }

    public function testEndAndArchiveStillRedirectsToClose(): void
    {
        $sut = $this->sut(['action' => 'End and Archive Conversation']);

        $redirect = new HttpResponse();
        $sut->expects('redirect->toRoute')
            ->with('licence/conversation/close', [], [], true)
            ->andReturn($redirect);
        $sut->expects('processFiles')->never();

        $this->assertSame($redirect, $this->handlePost($sut, $this->form(true)));
    }

    public function testUploadIsStoredAgainstTheCorrelationIdAsAnInternalDocument(): void
    {
        $command = $this->uploadCommandFor('form.pdf');

        $this->assertSame(self::CORRELATION_ID, $command->getCorrelationId());
        $this->assertFalse($command->getIsExternal());
        $this->assertSame('form.pdf', $command->getDescription());
        $this->assertSame('form.pdf', $command->getFilename());
        $this->assertSame(\Common\Category::CATEGORY_LICENSING, (int) $command->getCategory());
        $this->assertSame(\Common\Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS, (int) $command->getSubCategory());
        $this->assertNull($command->getMessagingConversation());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fileNameProvider')]
    public function testUploadIsAcceptedByTheApiWhateverTheFileIsCalled(
        string $fileName,
        string $descriptionTheApiKeeps
    ): void {
        $container = (new AnnotationBuilder())->createCommand($this->uploadCommandFor($fileName));

        $this->assertTrue($container->isValid(), json_encode($container->getMessages()));
        $this->assertSame($descriptionTheApiKeeps, $container->getDto()->getDescription());
    }

    public static function fileNameProvider(): \Iterator
    {
        yield 'plain name' => ['form.pdf', 'form.pdf'];
        yield 'dots and brackets' => ['Form to complete (v1.2).pdf', 'Form to complete v1.2.pdf'];
        yield 'one letter' => ['a.pdf', 'a.pdf'];
        yield 'one accented character' => ["\u{e9}.pdf", '.pdf'];
        yield 'no characters the api keeps' => ["\u{6587}\u{4ef6}.pdf", '.pdf'];
    }

    public function testUploadedFilesAreLookedUpByTheCorrelationId(): void
    {
        $sut = $this->sut(['action' => 'reply', 'correlationId' => self::CORRELATION_ID]);

        $response = m::mock(Response::class);
        $response->allows('isOk')->andReturn(true);
        $response->allows('getResult')->andReturn([['id' => 9]]);
        $sut->expects('handleQuery')
            ->with(m::on(fn (Documents $query): bool => $query->getCorrelationId() === self::CORRELATION_ID))
            ->andReturn($response);

        $this->assertSame([['id' => 9]], $sut->getUploadedFiles());
    }
}
