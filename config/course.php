<?php

return [

    /*
    | The address the course is served on. Only requests for this host reach the
    | course routes, so an environment that doesn't set it keeps the tool alone.
    */
    'domain' => env('COURSE_DOMAIN', 'course.localhost'),

    /*
    | Where links to the tool go. They must leave the course address: until first
    | rollout the course has its own database copy, so a case created on the
    | course address would never reach the real tool.
    */
    'tool_url' => rtrim(env('COURSE_TOOL_URL', 'http://127.0.0.1:8000'), '/'),

    /*
    | The analyzed case shown to strangers. Used only if it exists and is public;
    | otherwise the link goes to the list of public cases.
    */
    'example_case_id' => env('COURSE_EXAMPLE_CASE_ID'),

    /* An embeddable video address. Without one, no video block is shown. */
    'landing_video_url' => env('COURSE_LANDING_VIDEO_URL'),

    /* Without an address, the Contact link is not shown. */
    'contact_email' => env('COURSE_CONTACT_EMAIL'),

];
