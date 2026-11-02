<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\GemaraCase;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        return view('course.landing', [
            'toolLink' => $this->onTool(route('gemara_cases.create', [], false)),
            'exampleLink' => $this->onTool($this->examplePath()),
            'videoUrl' => config('course.landing_video_url'),
        ]);
    }

    /** The configured case only if it exists and is public; otherwise the public list. */
    private function examplePath(): string
    {
        $case = GemaraCase::whereKey(config('course.example_case_id'))
            ->where('public', true)
            ->first();

        return $case
            ? route('gemara_cases.show', $case, false)
            : route('gemara_cases.index', ['public' => 1], false);
    }

    /** route() builds on the current host, so a tool link needs the tool's address put on it. */
    private function onTool(string $path): string
    {
        return config('course.tool_url') . $path;
    }
}
