<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

use App\Http\Resources\JobResource;
use App\Jobs\ProcessPhotoJob;
use App\Models\Job;
use App\Services\ScanService;

/**
 * Handles background Job management via API endpoints.
 *
 * Provides:
 *  - Job listing and pagination.
 *  - Job dispatching with parameters.
 *  - Pending job count aggregation by type.
 *
 * Routes:
 *  - GET /api/jobs
 *  - GET /api/jobs/dispatch
 *  - GET /api/jobs/pending-count
 */
class JobController extends Controller
{
    /**
     * Retrieve one or more Jobs
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Job::class);

        $jobs = Job::picOJobs()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return JobResource::collection($jobs);
    }

    /**
     * Start a specific Job with given parameters
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function dispatchJob(Request $request): JsonResponse
    {
        $this->authorize('create', Job::class);

        $validated = $request->validate([
            'type'   => 'required|string|in:TraverseFolderJob',
            'params' => 'nullable|array'
        ]);

        $type   = $validated['type'];
        $params = $validated['params'] ?? [];

        switch ($type)
        {
            case 'TraverseFolderJob':
                app(ScanService::class)->run($params['path'] ?? null);
                break;
        }

        return response()->json([
            'status' => 'queued',
            'job'    => $type,
            'params' => $params
        ], 202);
    }

    /**
     * Retrieve the number of pending Jobs grouped by type
     *
     * @return JsonResponse
     */
    public function countPending(): JsonResponse
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
