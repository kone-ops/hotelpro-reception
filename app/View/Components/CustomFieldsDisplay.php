<?php

namespace App\View\Components;

use App\Services\FormConfigService;
use Illuminate\View\Component;

class CustomFieldsDisplay extends Component
{
    public $customFields;
    public $reservationData;
    public $formConfig;
    public $displayStyle; // 'table', 'list', 'inline', 'police-sheet'

    /**
     * Create a new component instance.
     */
    public function __construct(
        FormConfigService $formConfig,
        array $reservationData,
        string $displayStyle = 'list'
    ) {
        $this->formConfig = $formConfig;
        $this->reservationData = $reservationData;
        $this->displayStyle = $displayStyle;
        $this->customFields = $formConfig->getCustomFieldsWithValues($reservationData);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.custom-fields-display');
    }
}

