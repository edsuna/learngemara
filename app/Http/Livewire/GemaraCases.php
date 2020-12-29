<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\GemaraCase;
use Illuminate\Support\Facades\Auth;


class GemaraCases extends Component
{
    public $gemaraCases;
    public $selectedId = 0;

    private function getCases() {
        $this->gemaraCases = GemaraCase::join('tractates', 'gemara_cases.masechet', '=', 'tractates.english_name')
                ->where('user_id', Auth::id())
                ->orderBy('tractates.id')
                ->orderByRaw('daf * 1') // First treat the daf as a number
                ->orderBy('daf')        // Then take into account the amud
                ->get(['gemara_cases.id as case_id', 'gemara_cases.*', 'tractates.*']);

    }

    public function mount() {
        $this->getCases();
    }

    public function hydrate() {
        $this->selectedId = 0;
    }

    public function render()
    {
        return view('livewire.gemara-cases');
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
}
