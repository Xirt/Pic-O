<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Allowed settings and their validation rules.
     */
    private const ALLOWED_SETTINGS = [

        'site_name' => [
            'rules' => 'required|string|max:255',
        ],

        'media_root' => [
            'rules' => 'required|string|max:255',
        ],

        'session_persistent' => [
            'rules' => 'required|in:0,1',
        ],

    ];

    /**
     * Store or update settings via API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
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
