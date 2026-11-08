<?php

use App\Telegram\Handlers\AttachHandler;
use App\Telegram\Handlers\ConfirmationHandler;
use App\Telegram\Handlers\ConfirmSolutionHandler;
use App\Telegram\Handlers\MessageHandler;
use App\Telegram\Handlers\ModeratorHandler;
use App\Telegram\Handlers\QuestionHandler;
use App\Telegram\Handlers\RegisterQuestionHandler;
use App\Telegram\Handlers\SolvedHandler;
use App\Telegram\Middleware\RequireModerator;
use App\Telegram\Middleware\ThrottleCommand;

/** @var \SergiX44\Nutgram\Nutgram $bot */
$bot->onText('/moderate(?:@[A-Za-z0-9_]+)?(?:\s.*)?', QuestionHandler::class)
    ->middleware(RequireModerator::class)->middleware(ThrottleCommand::class);
$bot->onText('/question(?:@[A-Za-z0-9_]+)?(?:\s.*)?', RegisterQuestionHandler::class)
    ->middleware(RequireModerator::class)->middleware(ThrottleCommand::class);
$bot->onText('/attach(?:@[A-Za-z0-9_]+)?(?:\s.*)?', AttachHandler::class)
    ->middleware(RequireModerator::class)->middleware(ThrottleCommand::class);
$bot->onText('/solved(?:@[A-Za-z0-9_]+)?(?:\s.*)?', SolvedHandler::class)
    ->middleware(ThrottleCommand::class);
$bot->onText('/(?:start|help|status|mute|unmute|block|unblock)(?:@[A-Za-z0-9_]+)?(?:\s.*)?', ModeratorHandler::class)
    ->middleware(RequireModerator::class);
$bot->onCallbackQueryData('question_(?:confirm|cancel):[0-9]+', ConfirmationHandler::class);
$bot->onCallbackQueryData('solve:[a-f0-9]{24}:[0-9]+', ConfirmSolutionHandler::class);
$bot->onMessage(MessageHandler::class);
