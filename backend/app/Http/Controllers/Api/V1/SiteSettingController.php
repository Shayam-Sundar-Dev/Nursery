<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SiteSettingController extends Controller
{
    /**
     * Get public site settings for storefront and mobile applications.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => SiteSetting::getPublicSettings(),
        ]);
    }
}
