<?php

namespace Tests\Feature\Spec;

use App\Models\GemaraCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * docs/spec/course-landing.md
 *
 * Requests name the course address explicitly, because that is what selects
 * these routes. Assertions use the lang keys rather than quoting the copy, so
 * editing lang/en/course.php cannot break a test. COURSE-10 is geometry and
 * lives in the browser suite.
 */
class CourseLandingTest extends TestCase
{
    use RefreshDatabase;

    private const COURSE = 'http://course.localhost';
    private const TOOL = 'https://tool.example';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config()->set('course.tool_url', self::TOOL);
    }

    /** COURSE-01 - The course address serves the landing page. */
    #[Test]
    public function the_course_address_serves_the_landing_page(): void
    {
        $this->get(self::COURSE . '/')
            ->assertOk()
            ->assertSee(__('course.promise'))
            ->assertDontSee('We try to make learning Gemara accessible.');
    }

    /** COURSE-01 - Any other address still serves the tool's home page. */
    #[Test]
    public function any_other_address_still_serves_the_tool_home(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('We try to make learning Gemara accessible.')
            ->assertDontSee(__('course.promise'));
    }

    /** COURSE-02 - The page says what the course does for me. */
    #[Test]
    public function the_page_states_the_promise_in_the_main_heading(): void
    {
        $html = $this->get(self::COURSE . '/')->assertOk()->getContent();

        $this->assertStringContainsString('images/logo.png', $html);
        $this->assertMatchesRegularExpression(
            '~<h1[^>]*>\s*' . preg_quote(e(__('course.promise')), '~') . '\s*</h1>~',
            $html
        );
        $this->assertStringContainsString(e(__('course.supporting')), $html);
    }

    /** COURSE-03 - Enroll is the one primary action. */
    #[Test]
    public function enroll_leads_to_the_enroll_step_on_the_course_address(): void
    {
        $this->get(self::COURSE . '/')
            ->assertSee(__('course.enroll'))
            ->assertSee('href="' . self::COURSE . '/enroll"', false);
    }

    /** COURSE-04 - There is a door to the tool, on the tool's address. */
    #[Test]
    public function the_tool_link_leaves_the_course_address(): void
    {
        $this->get(self::COURSE . '/')
            ->assertSee(__('course.use_tool'))
            ->assertSee('href="' . self::TOOL . '/gemara_cases/create"', false)
            ->assertDontSee('href="' . self::COURSE . '/gemara_cases/create"', false);
    }

    /** COURSE-05 - The configured public example case is linked, on the tool's address. */
    #[Test]
    public function a_configured_public_example_case_is_linked(): void
    {
        $case = GemaraCase::factory()->create(['public' => true]);
        config()->set('course.example_case_id', $case->id);

        $this->get(self::COURSE . '/')
            ->assertOk()
            ->assertSee(__('course.example_case'))
            ->assertSee('href="' . self::TOOL . '/gemara_cases/' . $case->id . '"', false);
    }

    /** COURSE-05 - With no example case configured, the link goes to the public list. */
    #[Test]
    public function with_no_example_case_the_link_goes_to_the_public_list(): void
    {
        config()->set('course.example_case_id', null);

        $this->get(self::COURSE . '/')
            ->assertOk()
            ->assertSee('href="' . self::TOOL . '/gemara_cases?public=1"', false);
    }

    /** COURSE-05 - A private case is never linked, even if it is the configured one. */
    #[Test]
    public function a_private_example_case_falls_back_to_the_public_list(): void
    {
        $case = GemaraCase::factory()->create(['public' => false]);
        config()->set('course.example_case_id', $case->id);

        $this->get(self::COURSE . '/')
            ->assertOk()
            ->assertDontSee('/gemara_cases/' . $case->id . '"', false)
            ->assertSee('href="' . self::TOOL . '/gemara_cases?public=1"', false);
    }

    /** COURSE-05 - A configured case that no longer exists falls back to the public list. */
    #[Test]
    public function a_missing_example_case_falls_back_to_the_public_list(): void
    {
        config()->set('course.example_case_id', 999999);

        $this->get(self::COURSE . '/')
            ->assertOk()
            ->assertSee('href="' . self::TOOL . '/gemara_cases?public=1"', false);
    }

    /** COURSE-06 - The page explains how the course works. */
    #[Test]
    public function the_three_points_are_shown(): void
    {
        $response = $this->get(self::COURSE . '/');

        foreach (['point_1', 'point_2', 'point_3'] as $point) {
            $this->assertTrue(Lang::has('course.' . $point), "missing lang key $point");
            $response->assertSee(__('course.' . $point));
        }
    }

    /** COURSE-07 - The method is explained on the page. */
    #[Test]
    public function the_method_section_is_on_the_page(): void
    {
        $this->get(self::COURSE . '/')
            ->assertSee('id="about-the-method"', false)
            ->assertSee(__('course.about_method_heading'))
            ->assertSee(__('course.about_method_body'));
    }

    /** COURSE-08 - The navigation offers About the method and the configured Contact address. */
    #[Test]
    public function the_navigation_links_to_the_method_and_the_contact_address(): void
    {
        config()->set('course.contact_email', 'contact@example.test');

        $this->get(self::COURSE . '/')
            ->assertSee('href="#about-the-method"', false)
            ->assertSee('href="mailto:contact@example.test"', false);
    }

    /** COURSE-08 - With no contact address, the Contact link is not shown. */
    #[Test]
    public function no_contact_link_without_a_contact_address(): void
    {
        config()->set('course.contact_email', null);

        $this->get(self::COURSE . '/')
            ->assertOk()
            ->assertDontSee('mailto:', false);
    }

    /** COURSE-09 - A guest is offered Sign in. */
    #[Test]
    public function a_guest_is_offered_sign_in(): void
    {
        $this->get(self::COURSE . '/')
            ->assertSee(__('course.sign_in'))
            ->assertSee('href="' . self::COURSE . '/sign-in"', false)
            ->assertDontSee('action="' . self::COURSE . '/logout"', false);
    }

    /** COURSE-09 - A signed-in user sees their name and a way to sign out. */
    #[Test]
    public function a_signed_in_user_sees_their_name_and_a_sign_out(): void
    {
        $user = User::factory()->create(['name' => 'Course Student']);

        $this->actingAs($user)
            ->get(self::COURSE . '/')
            ->assertSee('Course Student')
            ->assertSee(__('course.sign_out'))
            ->assertSee('action="' . self::COURSE . '/logout"', false)
            ->assertDontSee('href="' . self::COURSE . '/sign-in"', false);
    }

    /** COURSE-11 - The video appears when an address is configured. */
    #[Test]
    public function the_video_appears_when_configured(): void
    {
        config()->set('course.landing_video_url', 'https://video.example/embed/abc');

        $this->get(self::COURSE . '/')
            ->assertSee('<iframe', false)
            ->assertSee('src="https://video.example/embed/abc"', false);
    }

    /** COURSE-11 - No video block without an address, and the rest of the page is unchanged. */
    #[Test]
    public function no_video_block_without_an_address(): void
    {
        config()->set('course.landing_video_url', null);

        $this->get(self::COURSE . '/')
            ->assertOk()
            ->assertDontSee('<iframe', false)
            ->assertSee(__('course.promise'))
            ->assertSee(__('course.enroll'));
    }

    /** COURSE-12 - Signing in from the course returns to the course. */
    #[Test]
    public function signing_in_from_the_course_returns_to_the_course(): void
    {
        $user = User::factory()->create(['password' => 'a-good-password']);

        $this->get(self::COURSE . '/sign-in')
            ->assertRedirect(self::COURSE . '/login');

        Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'a-good-password')
            ->call('authenticate')
            ->assertRedirect(self::COURSE);

        $this->assertAuthenticatedAs($user);
    }

    /** ENROLL-01 - Enrolling as a guest asks for an account and returns to the course. */
    #[Test]
    public function enrolling_as_a_guest_registers_and_returns_to_the_course(): void
    {
        $this->get(self::COURSE . '/enroll')
            ->assertRedirect(self::COURSE . '/register');

        Livewire::test('auth.register')
            ->set('name', 'New Student')
            ->set('email', 'new-student@example.test')
            ->set('password', 'a-good-password')
            ->set('passwordConfirmation', 'a-good-password')
            ->call('register')
            ->assertRedirect(self::COURSE);

        $this->assertAuthenticated();
    }

    /** ENROLL-02 - Enrolling while signed in does not ask to sign in. */
    #[Test]
    public function enrolling_while_signed_in_returns_with_a_confirmation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->followingRedirects()
            ->get(self::COURSE . '/enroll')
            ->assertOk()
            ->assertSee(__('course.enrolled_notice'))
            ->assertSee(__('course.promise'));
    }

    /** The enroll and sign-in steps exist only on the course address. */
    #[Test]
    public function the_course_routes_do_not_exist_on_other_addresses(): void
    {
        $this->get('/enroll')->assertNotFound();
        $this->get('/sign-in')->assertNotFound();
    }

    /** Every piece of copy the template reads exists, so nothing renders as a raw key. */
    #[Test]
    public function every_copy_key_exists(): void
    {
        $keys = ['promise', 'supporting', 'enroll', 'use_tool', 'example_case', 'how_heading',
            'point_1', 'point_2', 'point_3', 'about_method_heading', 'about_method_body', 'closing',
            'sign_in', 'sign_out', 'contact', 'about_method_link', 'enrolled_notice', 'meta_description'];

        foreach ($keys as $key) {
            $this->assertTrue(Lang::has('course.' . $key), "missing lang key $key");
        }
    }
}
