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
            ->status(request('status'))
            ->orderBy('id', 'desc')->paginate(15);

        $appliances = Appliance::pluck('title', 'id');

        return view('public.appliances.show', compact('appliance', 'questions', 'appliances'));
    }
}
