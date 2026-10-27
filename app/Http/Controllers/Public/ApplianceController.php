<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Filters\Questions\TextFilter;
use App\Models\Appliance;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Filters\Questions\StatusFilter;
use Illuminate\Pipeline\Pipeline;

class ApplianceController extends Controller
{

    public function show($slug)
    {
        $appliance = Appliance::where('slug', $slug)->firstOrFail();
        $questions = app(Pipeline::class)
            ->send($appliance->questions()->getQuery())
            ->through([StatusFilter::class])
            ->thenReturn()
            ->with('appliance', 'user')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $appliances = Appliance::pluck('title', 'id');

        return view('public.appliances.show', compact('appliance', 'questions', 'appliances'));
    }
}
