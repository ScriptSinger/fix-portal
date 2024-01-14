<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appliance;
use Illuminate\Http\Request;

class ApplianceController extends Controller
{
    public function show($slug)
    {


        $appliance = Appliance::where('slug', $slug)->firstOrFail();
        $questions = $appliance->questions()->orderBy('id', 'desc')->paginate(15);
        return view('public.appliances.show', compact('appliance', 'questions'));
    }
}
