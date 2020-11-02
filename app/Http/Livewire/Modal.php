<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Modal extends Component
{
    public $showFlag;
    public $modalContent;
    public $clickAway;

    public function render()
    {
        return view('livewire.modal');
    }
}
