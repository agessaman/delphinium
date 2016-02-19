<?php
namespace Backend\Widgets;

use Backend\Classes\FormWidgetBase;
use Backend\Formwidgets\ColorPicker;
use Backend\Widgets\Form\Form;// checkbox & radio ?

class CodePicker extends FormWidgetBase
{
    public function widgetDetails()
    {
        return [
            'name'        => 'Color Picker',
            'description' => 'Choose Color to use'
        ];
    }

    public function render() {
        // add ?
    
    }
}
?>