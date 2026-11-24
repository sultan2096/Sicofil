<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PhotoMap;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PhotoMapController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        // Décoder l'image base64
        $image = $request->input('image');
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'map_' . Str::random(10) . '.png';
        $imagePath = 'uploads/map_photos/' . $imageName;
        Storage::disk('public')->put($imagePath, base64_decode($image));

        $photo = PhotoMap::create([
            'image_path' => $imagePath,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);

        return response()->json(['success' => true, 'photo' => $photo]);
    }

    public function all()
    {
        return response()->json(PhotoMap::all());
    }
} 