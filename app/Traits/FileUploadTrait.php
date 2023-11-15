<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait FileUploadTrait
{

    public static function uploadFile(Request $request, string $attributeName = null, string $attributeValue = null, $dateFolder = true): ?string
    {

        if ($request->hasFile($attributeName)) {
            if ($attributeValue) {

                Storage::disk('public')->delete($attributeValue);
            }
            if ($dateFolder) {
                $folder = date('Y-m-d');
                return $request->file($attributeName)->store("$attributeName/$folder", 'public');
            }
            return $request->file($attributeName)->store("$attributeName/", 'public');
        } elseif ($attributeValue) {
            return $attributeValue;
        }
        return null;
    }
}
