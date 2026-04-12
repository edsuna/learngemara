<?php

namespace App\Livewire;

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
