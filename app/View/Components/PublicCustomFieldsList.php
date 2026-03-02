<?php

namespace App\View\Components;

use Illuminate\View\Component;

class PublicCustomFieldsList extends Component
{
    public function __construct(
        public $customFields,
        public int $section,
        public $formConfig,
        public ?float $position = null
    ) {}

    public function fields()
    {
        $collection = $this->customFields
            ->where('section', $this->section)
            ->where('active', true);

        if ($this->position !== null) {
            $collection = $collection->filter(function ($field) {
                $fieldPos = (float) ($field->position ?? 0);
                return abs($fieldPos - $this->position) < 0.1;
            });
        }

        return $collection->sortBy('position')->values();
    }

    public function render()
    {
        return view('components.public.custom-fields-list', [
            'fields' => $this->fields(),
        ]);
    }
}
