<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\Setting;

/**
 * Handles application Settings management via API endpoints.
 *
 * Provides:
 *  - Validation of allowed settings.
 *  - Creation and updating of application settings.
 *
 * Routes:
 *  - POST /api/settings
 */
class SettingController extends Controller
{
    /**
     * Allowed settings and their validation rules.
     *
     * @var array<string, array{rules: mixed}>
     */
    private const ALLOWED_SETTINGS = [

        'site_name' => [
            'rules' => 'required|string|max:255',
        ],

        'media_root' => [
            'rules' => 'required|string|max:255',
        ],

        'force_rescan' => [
            'rules' => 'required|in:0,1',
        ],

        'scanner_interval' => [
            'rules' => 'required|in:none,hourly,daily,weekly,monthly',
        ],

        'scanner_time' => [
            'rules' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
        ],

        'scanner_day_week' => [
            'rules' => 'required|integer|between:0,6',
        ],

        'scanner_day_month' => [
            'rules' => 'required|integer|between:1,31',
        ],

        'downscale_renders' => [
            'rules' => 'required|in:0,1',
        ],

        'cache_renders' => [
            'rules' => 'required|in:0,1',
        ],

        'session_persistent' => [
            'rules' => 'required|in:0,1',
        ],

        'album_name_tpl' => [
            'rules' => 'required|string|max:255',
        ],

        'album_sorting_type' => [
            'rules' => 'required|in:name,type,start_date,photos_count',
        ],

        'album_sorting_direction' => [
            'rules' => 'required|in:asc,desc',
        ],

    ];

    /**
     * Create or update one or more Settings
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('update', Setting::class);

        $input = $request->all();

        $rules = [];
        foreach (self::ALLOWED_SETTINGS as $key => $meta)
        {
            if (array_key_exists($key, $input))
            {
                $rules[$key] = $meta['rules'];
            }
        }

        $validated = validator($input, $rules)->validate();
        foreach ($validated as $key => $value)
        {
            Setting::updateOrCreate(
                ['key'   => $key],
                ['value' => $value],
            );
        }

        return response()->json(['message' => 'Settings saved']);
    }
}
