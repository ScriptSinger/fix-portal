<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Firmware;
use App\Models\Path;
use Illuminate\Http\Request;

class FirmwareController extends Controller
{
    public function index()
    {
        $firmwares = Firmware::paginate(50);
        return view('admin.firmwares.index', compact('firmwares',));
    }

    public function show(string $id)
    {
        $firmware = Firmware::findOrFail($id);

        return view('admin.firmwares.show', compact('firmware'));
    }

    public function downloadFile($filename)
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

        return view('admin.firmwares.search', compact('firmwares'));
    }
}
