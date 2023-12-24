<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Firmware;
use Illuminate\Http\Request;

class FirmwareController extends Controller
{
    public function index()
    {
        $firmwares = Firmware::paginate(50);
        return view('public.firmwares.index', compact('firmwares'));
    }

    public function show(string $id)
    {
        $firmware = Firmware::findOrFail($id);
        return view('public.firmwares.show', compact('firmware'));
    }

    public function download($filename)
    {
        $path = storage_path('app/public/firmwares/' . $filename);
        if (file_exists($path)) {
            return response()->download($path);
        } else {
            return redirect()->back()->with('error', 'Файл не найден');
        }
    }


    public function search(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|max:255',
        ]);

        $firmwares = Firmware::where(function ($query) use ($data) {
            $fields = [
                'title',
                // 'size',
                // 'date',
                'extension',
                'platform',
                // 'crc32',
                'data'
            ];

            foreach ($fields as $field) {
                $query->orWhere($field, 'like', '%' . $data['text'] . '%');
            }
        })->paginate(50)->appends(['text' => $data['text']]); // Append 'text' parameter to pagination links;

        return view('public.firmwares.search', compact('firmwares'));
    }
}
