<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\Submission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function index(Request $request): Response
    {
        $submissions = Submission::query()
            ->with(['process:id,name', 'bot:id,name'])
            ->when($request->filled('process_id'), fn ($q) => $q->where('process_id', $request->process_id))
            ->when($request->filled('platform'), fn ($q) => $q->where('platform', $request->platform))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('started_at')
            ->get();

        return Inertia::render('Submissions/Index', [
            'submissions' => $submissions,
            'processes' => Process::where('is_current_version', true)->get(['id', 'name']),
            'filters' => $request->only(['process_id', 'platform', 'status']),
        ]);
    }

    public function show(Submission $submission): Response
    {
        $submission->load([
            'process:id,name',
            'bot:id,name,platform',
            'values.field:id,label,step_id',
            'values.field.step:id,name',
        ]);

        return Inertia::render('Submissions/Show', [
            'submission' => $submission,
        ]);
    }
}
