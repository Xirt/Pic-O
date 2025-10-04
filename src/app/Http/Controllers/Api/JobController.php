<?php

namespace App\Http\Controllers\Api;

use App\Models\Job;
use App\Http\Resources\JobResource;
use App\Http\Controllers\Controller;

class JobController extends Controller
{
    /**
     * Get a paginated list of Pic-O jobs.
     */
    public function index()
    {
        $jobs = Job::picOJobs()
            ->orderBy('created_at', 'desc')
            ->select('id', 'payload', 'created_at')
            ->paginate(50);

        return JobResource::collection($jobs);
    }

    /**
     * Start a specific job with given parameters
     */
    public function dispatchJob(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:TraverseFolderJob,ProcessPhotoJob',
            'params' => 'nullable|array'
        ]);

        $type   = $validated['type'];
        $params = $validated['params'] ?? [];

        switch ($type) {

            case 'TraverseFolderJob':
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
     * Get count of pending Pic-O jobs by type (optimized, database-level).
     */
    public function countPending()
    {
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