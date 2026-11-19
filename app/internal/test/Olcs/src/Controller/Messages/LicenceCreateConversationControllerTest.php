<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Messages;

use Common\Data\Mapper\DefaultMapper;
use Common\Form\Form;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Create;
use Dvsa\Olcs\Transfer\Query\Messaging\Documents;
use Laminas\Form\Element\Hidden;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Navigation\Navigation;
use Laminas\Stdlib\Parameters;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Messages\LicenceCreateConversationController as Sut;

final class LicenceCreateConversationControllerTest extends MockeryTestCase
{
    private const string CORRELATION_ID = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';

    private Hidden $correlationIdOnForm;

    private function sut(?array $post): Sut
    {
        $sut = m::mock(Sut::class, [
            m::mock(TranslationHelperService::class),
            m::mock(FormHelperService::class),
            m::mock(FlashMessengerHelperService::class),
            m::mock(Navigation::class),
            m::mock(FileUploadHelperService::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $request = new Request();
        if ($post !== null) {
            $request->setMethod(Request::METHOD_POST);
            $request->setPost(new Parameters($post));
        }
        $sut->allows('getRequest')->andReturn($request);

        $params = m::mock(Params::class);
        $params->allows('fromPost')->withNoArgs()->andReturn($post ?? []);
        $params->allows('fromPost')->with('correlationId')->andReturn($post['correlationId'] ?? null);
        $sut->allows('params')->andReturn($params);

        return $sut;
    }

    private function form(): Form
    {
        $this->correlationIdOnForm = new Hidden('correlationId');

        $form = m::mock(Form::class);
        $form->allows('get')->with('correlationId')->andReturn($this->correlationIdOnForm);

        return $form;
    }

    private function alterFormForAdd(Sut $sut, Form $form): mixed
    {
        return (new \ReflectionMethod(Sut::class, 'alterFormForAdd'))->invoke($sut, $form, ['fields' => []]);
    }

    private function willCreateTheConversation(Sut $sut): bool
    {
        return (new \ReflectionProperty(Sut::class, 'persist'))->getValue($sut);
    }

    public function testGetGivesTheFormANewCorrelationId(): void
    {
        $sut = $this->sut(null);
        $form = $this->form();

        $sut->expects('processFiles')->never();

        $this->assertSame($form, $this->alterFormForAdd($sut, $form));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{40}$/', $this->correlationIdOnForm->getValue());
        $this->assertTrue($this->willCreateTheConversation($sut));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('uploadOrRemovePostProvider')]
    public function testAnUploadOrRemoveNeverCreatesTheConversationWhateverHappenedToTheFile(
        array $filePost,
        bool $processed
    ): void {
        $sut = $this->sut([
            'correlationId' => self::CORRELATION_ID,
            'fields' => ['messageSubject' => '3', 'messageContent' => 'A valid message'],
            'file' => $filePost,
        ]);
        $form = $this->form();

        $sut->expects('processFiles')
            ->with($form, 'file', m::type('callable'), m::type('callable'), m::type('callable'))
            ->andReturn($processed);

        $this->alterFormForAdd($sut, $form);

        $this->assertFalse($this->willCreateTheConversation($sut));
        $this->assertSame(self::CORRELATION_ID, $this->correlationIdOnForm->getValue());
    }

    public static function uploadOrRemovePostProvider(): \Iterator
    {
        yield 'upload sent by the page script' => [['upload' => 'Upload'], true];
        yield 'upload that was rejected' => [['upload' => 'Upload'], false];
        yield 'upload button pressed with the page script off' => [['upload' => ''], false];
        yield 'remove' => [['list' => ['file-9' => ['id' => '9', 'version' => '1', 'remove' => 'Remove']]], true];
        yield 'remove of a file that has already gone' => [
            ['list' => ['file-9' => ['id' => '9', 'version' => '1', 'remove' => 'Remove']]],
            false,
        ];
    }

    public function testSendWithFilesListedCreatesTheConversation(): void
    {
        $sut = $this->sut([
            'correlationId' => self::CORRELATION_ID,
            'fields' => ['messageSubject' => '3', 'messageContent' => 'A valid message'],
            'file' => [
                'fileCount' => '',
                '__messages__' => '',
                'list' => ['file-9' => ['id' => '9', 'version' => '1']],
            ],
            'form-actions' => ['submit' => ''],
            'security' => 'token',
        ]);

        $sut->expects('processFiles')->andReturn(false);

        $this->alterFormForAdd($sut, $this->form());

        $this->assertTrue($this->willCreateTheConversation($sut));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('noUsableCorrelationIdProvider')]
    public function testCreateFromAPageWithoutACorrelationIdStillWorks(array $post): void
    {
        $sut = $this->sut($post + ['fields' => ['messageSubject' => '3', 'messageContent' => 'A valid message']]);

        $sut->expects('processFiles')->never();

        $this->alterFormForAdd($sut, $this->form());

        $this->assertTrue($this->willCreateTheConversation($sut));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{40}$/', $this->correlationIdOnForm->getValue());
        $this->assertNull($this->commandFrom($sut, ['messageSubject' => '3'])->getCorrelationId());
    }

    public static function noUsableCorrelationIdProvider(): \Iterator
    {
        yield 'field missing' => [[]];
        yield 'field empty' => [['correlationId' => '']];
        yield 'field is not text' => [['correlationId' => ['a']]];
    }

    public function testCommandCarriesTheCorrelationIdWithTheFormFields(): void
    {
        $sut = $this->sut(['correlationId' => self::CORRELATION_ID]);

        $command = $this->commandFrom($sut, [
            'messageSubject' => '3',
            'messageContent' => 'A valid message',
            'licence' => '7',
            'application' => '',
        ]);

        $this->assertSame(self::CORRELATION_ID, $command->getCorrelationId());
        $this->assertSame(7, (int) $command->getLicence());
        $this->assertSame(3, (int) $command->getMessageSubject());
        $this->assertSame('A valid message', $command->getMessageContent());
    }

    private function commandFrom(Sut $sut, array $fields): Create
    {
        return Create::create(
            (new \ReflectionMethod(Sut::class, 'mapFromForm'))->invoke($sut, DefaultMapper::class, ['fields' => $fields])
        );
    }

    public function testUploadedFilesAreLookedUpByTheCorrelationId(): void
    {
        $sut = $this->sut(['correlationId' => self::CORRELATION_ID]);

        $response = m::mock(Response::class);
        $response->allows('isOk')->andReturn(true);
        $response->allows('getResult')->andReturn([['id' => 9]]);
        $sut->expects('handleQuery')
            ->with(m::on(fn (Documents $query): bool => $query->getCorrelationId() === self::CORRELATION_ID))
            ->andReturn($response);

        $this->assertSame([['id' => 9]], $sut->getUploadedFiles());
    }
}
