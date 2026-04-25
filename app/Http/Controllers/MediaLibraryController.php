<?php

namespace App\Http\Controllers;

use App\Models\MediaLibrary;
use Illuminate\Http\Request;

class MediaLibraryController extends Controller
{
   
    public function index()
    {
        $media = auth()->user()->mediaLibrary()
            ->latest()
            ->get();

        return view('media.index', compact('media'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5000',
        ]);

        $file = $request->file('file');
        $path = $file->store('media-library', 'public');

        MediaLibrary::create([
            'user_id'       => auth()->id(),
            'filename'      => $file->hashName(),
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'type'          => 'image',
            'size'          => $file->getSize(),
        ]);

        return back()->with('success', 'Image uploaded!');
    }


    public function destroy(MediaLibrary $media)
    {
        if ($media->user_id !== auth()->id()) {
            abort(403);
        }

        \Storage::disk('public')->delete($media->path);
        $media->delete();

        return back()->with('success', 'Image deleted!');
    }
}