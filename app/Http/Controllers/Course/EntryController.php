<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * The doors into an account from the course. Login and Register both honour the
 * intended URL, so setting it here is what brings a visitor back to the course
 * instead of the tool's home.
 */
class EntryController extends Controller
{
    /** First slice of enrolling: the goal question and pledge land in this same step later. */
    public function enroll(): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('course.landing')->with('status', __('course.enrolled_notice'));
        }

        return $this->toAccountPage('register');
    }

    public function signIn(): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('course.landing');
        }

        return $this->toAccountPage('login');
    }

    private function toAccountPage(string $route): RedirectResponse
    {
        redirect()->setIntendedUrl(route('course.landing'));

        return redirect()->route($route);
    }
}
