<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Filters\Questions\TextFilter;
use App\Models\Appliance;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

class ApplianceController extends Controller
{

    public function show($slug)
    {
        $appliance = Appliance::where('slug', $slug)->firstOrFail();
        $questions = $appliance->questions()->with('appliance', 'user')
            ->when(request('status') === 'solved', fn ($q) => $q->whereNotNull('solved_at'))
            ->when(request('status') === 'open', fn ($q) => $q->whereNull('solved_at')->where('created_at', '>=', \App\Models\Question::SOLUTION_TRACKING_SINCE))
            ->orderBy('id', 'desc')->paginate(15);

        $appliances = Appliance::pluck('title', 'id');

        return view('public.appliances.show', compact('appliance', 'questions', 'appliances'));
    }
}
