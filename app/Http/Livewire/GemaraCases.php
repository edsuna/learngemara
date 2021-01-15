<?php

namespace App\Http\Livewire;

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
    public $masechet;
    public $daf;

    private function getCases() {
        $query = GemaraCase::join('tractates', 'gemara_cases.masechet', '=', 'tractates.english_name')
                ->orderBy('tractates.id')
                ->orderByRaw('daf * 1') // First treat the daf as a number
                ->orderBy('daf');       // Then take into account the amud
        if (!$this->public) {
            $query = $query->where('user_id', Auth::id());
        }
        else {
            $query = $query->where('public', 1);
        }

        if ($this->masechet) {
            $query = $query->where('masechet', $this->masechet);
        }
        $this->gemaraCases = $query->select('gemara_cases.id as case_id', 'gemara_cases.*', 'tractates.*')->paginate(25);
    }

    public function mount() {
        $this->selectedId = 0;
        $this->getCases();
    }

    public function hydrate() {
        $this->selectedId = 0;
    }

    public function render()
    {
        $this->getCases();
        return view('livewire.gemara-cases',
                    ['gemaraCases' => $this->gemaraCases]);
    }

    public function confirmRemove($id) {
        $this->selectedId = $id;
        $this->getCases();
    }

    public function clearSelected() {
        $this->selectedId = 0;
        $this->getCases();
    }

    public function removeGemaraCase($id) {
        // Remove the case from the database
        GemaraCase::destroy($id);

        // Remove the case from the collection
        $this->getCases();
    }

    public function updateMasechet() {
        $this->resetPage();
        $this->getCases();
    }
}
