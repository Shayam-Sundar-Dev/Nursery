<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HomeSlider;
use Illuminate\Http\JsonResponse;

class HomeSliderController extends Controller
{
    /**
     * Get active home page hero banners / promo sliders.
     */
    public function index(): JsonResponse
    {
        $sliders = HomeSlider::active()->get();

        return response()->json([
            'success' => true,
            'data' => $sliders,
        ]);
    }
}
