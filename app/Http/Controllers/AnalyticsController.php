<?php

namespace App\Http\Controllers;

use App\Contracts\SocialMediaProvider;
use App\Models\FacebookPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private SocialMediaProvider $facebook) {}

    public function pageInsights(Request $request, FacebookPage $page): JsonResponse
    {
      
        if ($page->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$page->isTokenValid()) {
            return response()->json(['error' => 'Token expired, please reconnect Facebook.'], 401);
        }

        $metric = $request->get('metric', 'page_impressions,page_engaged_users,page_fans');
        $period = $request->get('period', 'day');

        $data = $this->facebook->getPageInsights(
            $page->access_token,
            $page->page_id,
            $metric,
            $period
        );

        return response()->json(['data' => $data]);
    }

    public function postInsights(Request $request): JsonResponse
    {
        $request->validate([
            'post_id' => 'required|string',
            'page_id' => 'required|exists:facebook_pages,id',
        ]);

        $page = FacebookPage::findOrFail($request->page_id);

        if ($page->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$page->isTokenValid()) {
            return response()->json(['error' => 'Token expired.'], 401);
        }

        $data = $this->facebook->getPostInsights(
            $page->access_token,
            $request->post_id
        );

        return response()->json(['data' => $data]);
    }
}