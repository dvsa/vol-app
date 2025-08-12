<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    // Letter Type
    QueryHandler\Letter\LetterType\Get::class => IsInternalUser::class,
    QueryHandler\Letter\LetterType\GetList::class => IsInternalUser::class,
    CommandHandler\Letter\LetterType\Create::class => IsInternalUser::class,
    CommandHandler\Letter\LetterType\Update::class => IsInternalUser::class,
    CommandHandler\Letter\LetterType\Delete::class => IsInternalUser::class,
    
    // Master Template
    QueryHandler\Letter\MasterTemplate\Get::class => IsInternalUser::class,
    QueryHandler\Letter\MasterTemplate\GetList::class => IsInternalUser::class,
    CommandHandler\Letter\MasterTemplate\Create::class => IsInternalUser::class,
    CommandHandler\Letter\MasterTemplate\Update::class => IsInternalUser::class,
    CommandHandler\Letter\MasterTemplate\Delete::class => IsInternalUser::class,
    
    // Letter Section
    QueryHandler\Letter\LetterSection\Get::class => IsInternalUser::class,
    QueryHandler\Letter\LetterSection\GetList::class => IsInternalUser::class,
    CommandHandler\Letter\LetterSection\Create::class => IsInternalUser::class,
    CommandHandler\Letter\LetterSection\Update::class => IsInternalUser::class,
    CommandHandler\Letter\LetterSection\Delete::class => IsInternalUser::class,
    
    // Letter Issue
    QueryHandler\Letter\LetterIssue\Get::class => IsInternalUser::class,
    QueryHandler\Letter\LetterIssue\GetList::class => IsInternalUser::class,
    CommandHandler\Letter\LetterIssue\Create::class => IsInternalUser::class,
    CommandHandler\Letter\LetterIssue\Update::class => IsInternalUser::class,
    CommandHandler\Letter\LetterIssue\Delete::class => IsInternalUser::class,
    
    // Letter Todo
    QueryHandler\Letter\LetterTodo\Get::class => IsInternalUser::class,
    QueryHandler\Letter\LetterTodo\GetList::class => IsInternalUser::class,
    CommandHandler\Letter\LetterTodo\Create::class => IsInternalUser::class,
    CommandHandler\Letter\LetterTodo\Update::class => IsInternalUser::class,
    CommandHandler\Letter\LetterTodo\Delete::class => IsInternalUser::class,
    
    // Letter Appendix
    QueryHandler\Letter\LetterAppendix\Get::class => IsInternalUser::class,
    QueryHandler\Letter\LetterAppendix\GetList::class => IsInternalUser::class,
    CommandHandler\Letter\LetterAppendix\Create::class => IsInternalUser::class,
    CommandHandler\Letter\LetterAppendix\Update::class => IsInternalUser::class,
    CommandHandler\Letter\LetterAppendix\Delete::class => IsInternalUser::class,
    
    // Letter Instance
    QueryHandler\Letter\LetterInstance\Get::class => IsInternalUser::class,
    QueryHandler\Letter\LetterInstance\GetList::class => IsInternalUser::class,
    CommandHandler\Letter\LetterInstance\Create::class => IsInternalUser::class,
    CommandHandler\Letter\LetterInstance\Update::class => IsInternalUser::class,
    
    // Letter Test Data
    QueryHandler\Letter\LetterTestData\Get::class => IsInternalUser::class,
    QueryHandler\Letter\LetterTestData\GetList::class => IsInternalUser::class,
    CommandHandler\Letter\LetterTestData\Create::class => IsInternalUser::class,
    CommandHandler\Letter\LetterTestData\Update::class => IsInternalUser::class,
    CommandHandler\Letter\LetterTestData\Delete::class => IsInternalUser::class,
];