<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Resources\JobResource;
use App\Jobs\TraverseFolderJob;
use App\Jobs\ProcessPhotoJob;
use App\Models\Job;

class JobController extends Controller
{
    /**
     * Retrieve one or more Jobs
     * GET /api/jobs
     */
    public function index()
    {
        $this->authorize('viewAny', Job::class);

        $jobs = Job::picOJobs()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return JobResource::collection($jobs);
    }

    /**
     * Starts a specific Job with given parameters
     * GET /api/jobs/dispatch
     */
    public function dispatchJob(Request $request)
    {
        $this->authorize('create', Job::class);

        $validated = $request->validate([
            'type'   => 'required|string|in:TraverseFolderJob,ProcessPhotoJob',
            'params' => 'nullable|array'
        ]);

        $type   = $validated['type'];
        $params = $validated['params'] ?? [];

        switch ($type)
        {
            case 'TraverseFolderJob':
                $params['path'] = resource_path(!empty($params['path']) ? $params['path'] : config('settings.media_root'));
                TraverseFolderJob::dispatch(...$params);
                break;

            case 'ProcessPhotoJob':
                ProcessPhotoJob::dispatch(...$params);
                break;
        }

        return response()->json([
            'status' => 'queued',
            'job'    => $type,
            'params' => $params
        ], 202);
    }

    /**
     * Get the number of pending Jobs by type
     * GET /api/jobs/pending-count
     */
    public function countPending()
    {
        $this->authorize('viewAny', Job::class);

        $counts = Job::picOJobs()
            ->selectRaw("
                CASE
                    WHEN payload LIKE '%TraverseFolderJob%' THEN 'TraverseFolderJob'
                    WHEN payload LIKE '%ProcessPhotoJob%' THEN 'ProcessPhotoJob'
                END as type,
                COUNT(*) as count
            ")
            ->groupBy('type')
            ->get();

        return response()->json($counts);
    }
}