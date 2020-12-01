<?php

namespace App\Http\Livewire;

use Livewire\Component;

class TopBar extends Component
{
    public $englishTitle;
    public $hebrewTitle;

    public function render()
    {
        return view('livewire.top-bar');
    }
}
