<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\GemaraCase;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class GemaraCases extends Component
{
    use WithPagination;

    private $gemaraCases;
    public $selectedId;
    public $public;

    #[Url]
    public $masechet = '';

    #[Url]
    public $daf = '';

    #[Url]
    public $searchText = '';

    private function getCases()
    {
        $query = GemaraCase::join('tractates', 'gemara_cases.masechet', '=', 'tractates.english_name')
                ->orderBy('tractates.id')
                ->orderByRaw('daf * 1')
                ->orderBy('daf');
        if (!$this->public) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('public', 1);
        }

        if ($this->masechet) {
            $query->where('masechet', $this->masechet);
        }

        if ($this->daf) {
            $query->where('daf', $this->daf);
        }

        if ($this->searchText) {
            $query->where(function ($query) {
                $fields = array_merge(['title', 'act'], GemaraCase::inputConditions);
                $query->where('gemara_text', 'like', "%{$this->searchText}%");

                foreach ($fields as $field) {
                    $query->orWhere($field, 'like', "%{$this->searchText}%");
                }
            });
        }

        $this->gemaraCases = $query->select('gemara_cases.id as case_id', 'gemara_cases.*', 'tractates.*')->paginate(25);
    }

    public function mount()
    {
        $this->selectedId = 0;
        $this->getCases();
    }

    public function hydrate()
    {
        $this->selectedId = 0;
    }

    public function render()
    {
        $this->getCases();
        return view('livewire.gemara-cases',
                    ['gemaraCases' => $this->gemaraCases]);
    }

    public function confirmRemove($id)
    {
        $this->selectedId = $id;
        $this->getCases();
    }

    public function clearSelected()
    {
        $this->selectedId = 0;
        $this->getCases();
    }

    public function removeGemaraCase($id)
    {
        // Livewire actions are callable by anyone who can render the component,
        // and the public case list renders for guests. Hiding the delete button
        // in the Blade template is presentation, not authorization -- without
        // this check any visitor could delete any case by id.
        $gemaraCase = GemaraCase::find($id);

        if (!$gemaraCase || $gemaraCase->user_id !== Auth::id()) {
            abort(403);
        }

        $gemaraCase->delete();
        $this->getCases();
    }

    public function updateMasechet()
    {
        $this->resetPage();
        $this->getCases();
    }
}
