<?php

namespace App\Http\Controllers;

use App\Models\PostTemplate;
use Illuminate\Http\Request;

class PostTemplateController extends Controller
{
    public function index()
    {
        $templates = PostTemplate::where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json(['templates' => $templates]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $template = PostTemplate::create([
            'user_id' => auth()->id(),
            'name'    => $request->name,
            'content' => $request->content,
        ]);

        return response()->json(['success' => true, 'template' => $template]);
    }

    public function destroy(PostTemplate $template)
    {
        if ($template->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $template->delete();

        return response()->json(['success' => true]);
    }
}