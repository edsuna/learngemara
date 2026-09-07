<?php

namespace Tests\Feature\Spec;

use App\Models\GemaraCase;
use App\Models\Tractate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * docs/spec/case-list.md
 *
 * Ordering, pagination and the tractates join had no coverage at all. They are
 * the parts most exposed by the Stage 0d location migration: the ordering
 * clauses reference `daf` and the join key is `masechet`, both of which move to
 * `case_locations`. A silent break in any of them is invisible to every other
 * test in the suite.
 */
class CaseListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function caseOn(string $masechet, string $daf, array $overrides = []): GemaraCase
    {
        return GemaraCase::factory()->create(array_replace([
            'masechet' => $masechet,
            'daf' => $daf,
            'public' => true,
        ], $overrides));
    }

    /** LIST-01 - The public list shows public cases to anyone. */
    #[Test]
    public function the_public_list_shows_public_cases_to_a_guest(): void
    {
        $this->caseOn('Berakhot', '2a', ['title' => 'a public case']);

        Livewire::test('gemara-cases', ['public' => true])
            ->assertOk()
            ->assertSee('a public case');
    }

    /** LIST-02 - The public list excludes private cases. */
    #[Test]
    public function the_public_list_excludes_private_cases(): void
    {
        $this->caseOn('Berakhot', '2a', ['title' => 'a public case']);
        $this->caseOn('Berakhot', '3a', ['title' => 'a private case', 'public' => false]);

        Livewire::test('gemara-cases', ['public' => true])
            ->assertSee('a public case')
            ->assertDontSee('a private case');
    }

    /** LIST-03 - My list shows only my own cases. */
    #[Test]
    public function my_list_shows_only_my_cases(): void
    {
        $me = User::factory()->create();
        $someoneElse = User::factory()->create();
        $this->caseOn('Berakhot', '2a', ['title' => 'mine', 'user_id' => $me->id]);
        $this->caseOn('Berakhot', '3a', ['title' => 'theirs', 'user_id' => $someoneElse->id]);

        Livewire::actingAs($me)->test('gemara-cases', ['public' => false])
            ->assertSee('mine')
            ->assertDontSee('theirs');
    }

    /** LIST-04 - My list includes my private cases. */
    #[Test]
    public function my_list_includes_my_private_cases(): void
    {
        $me = User::factory()->create();
        $this->caseOn('Berakhot', '2a', ['title' => 'my private case', 'user_id' => $me->id, 'public' => false]);

        Livewire::actingAs($me)->test('gemara-cases', ['public' => false])
            ->assertSee('my private case');
    }

    /**
     * LIST-05 - Cases are ordered by tractate, then daf number, then amud.
     *
     * `orderByRaw('daf * 1')` is what puts 7b before 10a; `daf` is a string, so
     * a plain sort would order "10a" before "7a". Both clauses are load-bearing.
     */
    #[Test]
    public function cases_are_ordered_by_tractate_then_daf_number_then_amud(): void
    {
        // Order the tractates by their canonical id, not alphabetically.
        $first = Tractate::orderBy('id')->first();
        $second = Tractate::orderBy('id')->skip(1)->first();

        $this->caseOn($second->english_name, '2a', ['title' => 'FOURTH']);
        $this->caseOn($first->english_name, '10a', ['title' => 'THIRD']);
        $this->caseOn($first->english_name, '2a', ['title' => 'FIRST']);
        $this->caseOn($first->english_name, '7b', ['title' => 'SECOND']);

        Livewire::test('gemara-cases', ['public' => true])
            ->assertSeeInOrder(['FIRST', 'SECOND', 'THIRD', 'FOURTH']);
    }

    /** LIST-06 - The list paginates at 25. */
    #[Test]
    public function the_list_paginates_at_twenty_five(): void
    {
        for ($i = 0; $i < 26; $i++) {
            $this->caseOn('Berakhot', (2 + $i) . 'a');
        }

        $paginator = Livewire::test('gemara-cases', ['public' => true])->viewData('gemaraCases');

        $this->assertSame(25, $paginator->count(), 'first page holds 25 rows');
        $this->assertSame(26, $paginator->total());
        $this->assertTrue($paginator->hasPages());
    }

    /** LIST-07 - Filtering by masechet narrows the list. */
    #[Test]
    public function filtering_by_masechet_narrows_the_list(): void
    {
        $this->caseOn('Berakhot', '2a', ['title' => 'in berakhot']);
        $this->caseOn('Shabbat', '2a', ['title' => 'in shabbat']);

        Livewire::test('gemara-cases', ['public' => true])
            ->set('masechet', 'Shabbat')
            ->assertSee('in shabbat')
            ->assertDontSee('in berakhot');
    }

    /** LIST-08 - Filtering by daf narrows the list. */
    #[Test]
    public function filtering_by_daf_narrows_the_list(): void
    {
        $this->caseOn('Berakhot', '2a', ['title' => 'on daf two']);
        $this->caseOn('Berakhot', '10b', ['title' => 'on daf ten']);

        Livewire::test('gemara-cases', ['public' => true])
            ->set('masechet', 'Berakhot')
            ->set('daf', '10b')
            ->assertSee('on daf ten')
            ->assertDontSee('on daf two');
    }

    /** LIST-10 - Changing masechet returns to page one. */
    #[Test]
    public function changing_the_masechet_filter_returns_to_page_one(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->caseOn('Berakhot', (2 + $i) . 'a');
        }

        // WithPagination keeps the page in the `paginators` array, not a
        // public $page property.
        Livewire::test('gemara-cases', ['public' => true])
            ->set('paginators.page', 2)
            ->assertSet('paginators.page', 2)
            ->call('updateMasechet')
            ->assertSet('paginators.page', 1);
    }

    /** LIST-11 - Search matches the gemara text. */
    #[Test]
    public function search_matches_the_gemara_text(): void
    {
        $this->caseOn('Berakhot', '2a', ['title' => 'wanted', 'gemara_text' => 'a very distinctive phrase']);
        $this->caseOn('Berakhot', '3a', ['title' => 'unwanted', 'gemara_text' => 'something else entirely']);

        Livewire::test('gemara-cases', ['public' => true])
            ->set('searchText', 'very distinctive')
            ->assertSee('wanted')
            ->assertDontSee('unwanted');
    }

    /**
     * LIST-12 - Search also matches title, act and the input conditions.
     *
     * The orWhere loop over title, act and all eight condition columns was
     * untested; only the gemara_text branch had any coverage.
     */
    #[Test]
    public function search_matches_title_act_and_input_conditions(): void
    {
        // Every one of these columns is itself searchable, so a text marker
        // cannot be held distinct from the needle. Assert on the returned rows.
        foreach (['title', 'act', 'who', 'consequences'] as $field) {
            $needle = 'needle-' . $field;
            $case = $this->caseOn('Berakhot', '2a', [$field => $needle]);

            $rows = Livewire::test('gemara-cases', ['public' => true])
                ->set('searchText', $needle)
                ->viewData('gemaraCases');

            $this->assertSame(1, $rows->total(), "searching {$field} should match exactly one case");
            $this->assertSame($case->id, $rows->first()->case_id, "searching {$field} matched the wrong case");
        }
    }

    /** LIST-13 - A search matching nothing empties the list. */
    #[Test]
    public function a_search_matching_nothing_returns_no_rows(): void
    {
        $this->caseOn('Berakhot', '2a', ['title' => 'a case']);

        $paginator = Livewire::test('gemara-cases', ['public' => true])
            ->set('searchText', 'nothing will ever match this')
            ->viewData('gemaraCases');

        $this->assertSame(0, $paginator->total());
    }

    /**
     * LIST-14 - The Hebrew tractate name is available to the row.
     *
     * It arrives as $case->name via the `tractates.*` in the select list. A
     * third join that reintroduces a `name` column, or drops the wildcard,
     * silently blanks the Hebrew column -- and nothing else would notice.
     */
    #[Test]
    public function rows_carry_the_hebrew_tractate_name(): void
    {
        $tractate = Tractate::where('english_name', 'Berakhot')->firstOrFail();
        $this->caseOn('Berakhot', '2a', ['title' => 'a case']);

        $row = Livewire::test('gemara-cases', ['public' => true])->viewData('gemaraCases')->first();

        $this->assertSame($tractate->name, $row->name, 'the Hebrew name comes from the tractates join');
        $this->assertNotNull($row->case_id, 'gemara_cases.id must stay aliased to case_id');
    }

    /** LIST-16 - Cancelling a delete leaves the case alone. */
    #[Test]
    public function cancelling_a_delete_leaves_the_case(): void
    {
        $me = User::factory()->create();
        $case = $this->caseOn('Berakhot', '2a', ['user_id' => $me->id]);

        Livewire::actingAs($me)->test('gemara-cases', ['public' => false])
            ->call('confirmRemove', $case->id)
            ->assertSet('selectedId', $case->id)
            ->call('clearSelected')
            ->assertSet('selectedId', 0);

        $this->assertDatabaseHas('gemara_cases', ['id' => $case->id]);
    }
}
