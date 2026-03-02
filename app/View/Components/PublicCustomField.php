<?php

namespace App\View\Components;

use Illuminate\View\Component;

class PublicCustomField extends Component
{
    public function __construct(
        public $field,
        public $formConfig
    ) {}

    public function render()
    {
        return view('components.public.custom-field');
    }
}
