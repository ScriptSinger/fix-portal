<?php

namespace App\Http\Filters\Questions;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** ?status=solved (решённые), ?status=open (без решения; только вопросы после запуска отметок) или ?status=unanswered (без ответов от других, любой давности). */
class StatusFilter
{
    public function __construct(private readonly Request $request)
    {
    }

    public function handle(Builder $builder, \Closure $next)
    {
        match ($this->request->input('status')) {
            'solved' => $builder->whereNotNull('solved_at'),
            'open' => $builder->whereNull('solved_at')->where('created_at', '>=', Question::SOLUTION_TRACKING_SINCE),
            'unanswered' => $builder->unanswered(),
            default => null,
        };

        return $next($builder);
    }
}
