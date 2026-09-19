<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProcessFieldRequest;
use App\Http\Requests\UpdateProcessFieldRequest;
use App\Models\Process;
use App\Models\ProcessField;
use App\Models\ProcessStep;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProcessFieldController extends Controller
{
    public function index(Process $process, ProcessStep $step): Response
    {
        return Inertia::render('Fields/Index', [
            'process' => $process,
            'step' => $step,
            'fields' => $step->fields()->orderBy('display_order')->get(),
        ]);
    }

    public function create(Process $process, ProcessStep $step): Response
    {
        return Inertia::render('Fields/Form', [
            'process' => $process,
            'step' => $step,
            'field' => null,
        ]);
    }

    public function store(StoreProcessFieldRequest $request, Process $process, ProcessStep $step): RedirectResponse
    {
        $maxOrder = $step->fields()->max('display_order');

        $step->fields()->create([
            'label' => $request->label,
            'field_type' => $request->field_type,
            'is_required' => $request->field_type === 'boolean' ? false : $request->boolean('is_required'),
            'options' => $request->field_type === 'select' ? $request->input('options') : null,
            'display_order' => $maxOrder === null ? 0 : $maxOrder + 1,
        ]);

        return redirect()
            ->route('processes.steps.fields.index', [$process, $step])
            ->with('success', 'فیلد با موفقیت ساخته شد.');
    }

    public function edit(Process $process, ProcessStep $step, ProcessField $field): Response
    {
        return Inertia::render('Fields/Form', [
            'process' => $process,
            'step' => $step,
            'field' => $field,
        ]);
    }

    public function update(UpdateProcessFieldRequest $request, Process $process, ProcessStep $step, ProcessField $field): RedirectResponse
    {
        $field->update([
            'label' => $request->label,
            'field_type' => $request->field_type,
            'is_required' => $request->field_type === 'boolean' ? false : $request->boolean('is_required'),
            'options' => $request->field_type === 'select' ? $request->input('options') : null,
        ]);

        return redirect()
            ->route('processes.steps.fields.index', [$process, $step])
            ->with('success', 'فیلد با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Process $process, ProcessStep $step, ProcessField $field): RedirectResponse
    {
        if ($process->submissions()->exists()) {
            return back()->with('error', 'این فرآیند دارای ثبت است و حذف فیلد مجاز نیست.');
        }

        $field->delete();

        return back()->with('success', 'فیلد حذف شد.');
    }
}
