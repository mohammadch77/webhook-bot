<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProcessStepRequest;
use App\Http\Requests\UpdateProcessStepRequest;
use App\Models\Process;
use App\Models\ProcessStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProcessStepController extends Controller
{
    public function index(Process $process): Response
    {
        return Inertia::render('Steps/Index', [
            'process' => $process,
            'steps' => $process->steps()->orderBy('display_order')->get(),
        ]);
    }

    public function create(Process $process): Response
    {
        return Inertia::render('Steps/Form', [
            'process' => $process,
            'step' => null,
        ]);
    }

    public function store(StoreProcessStepRequest $request, Process $process): RedirectResponse
    {
        $maxOrder = $process->steps()->max('display_order');

        $process->steps()->create([
            'name' => $request->name,
            'display_order' => $maxOrder === null ? 0 : $maxOrder + 1,
        ]);

        return redirect()->route('processes.steps.index', $process)->with('success', 'مرحله با موفقیت ساخته شد.');
    }

    public function edit(Process $process, ProcessStep $step): Response
    {
        return Inertia::render('Steps/Form', [
            'process' => $process,
            'step' => $step,
        ]);
    }

    public function update(UpdateProcessStepRequest $request, Process $process, ProcessStep $step): RedirectResponse
    {
        $step->update(['name' => $request->name]);

        return redirect()->route('processes.steps.index', $process)->with('success', 'مرحله با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Process $process, ProcessStep $step): RedirectResponse
    {
        if ($process->submissions()->exists()) {
            return back()->with('error', 'این فرآیند دارای ثبت است و حذف مرحله مجاز نیست.');
        }

        $step->delete();

        return back()->with('success', 'مرحله حذف شد.');
    }

    public function moveUp(Process $process, ProcessStep $step): RedirectResponse
    {
        $previous = $process->steps()
            ->where('display_order', '<', $step->display_order)
            ->orderByDesc('display_order')
            ->first();

        if ($previous) {
            DB::transaction(function () use ($step, $previous) {
                [$a, $b] = [$step->display_order, $previous->display_order];
                $step->update(['display_order' => $b]);
                $previous->update(['display_order' => $a]);
            });
        }

        return back();
    }

    public function moveDown(Process $process, ProcessStep $step): RedirectResponse
    {
        $next = $process->steps()
            ->where('display_order', '>', $step->display_order)
            ->orderBy('display_order')
            ->first();

        if ($next) {
            DB::transaction(function () use ($step, $next) {
                [$a, $b] = [$step->display_order, $next->display_order];
                $step->update(['display_order' => $b]);
                $next->update(['display_order' => $a]);
            });
        }

        return back();
    }
}
