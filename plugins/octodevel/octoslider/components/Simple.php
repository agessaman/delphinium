<?php namespace OctoDevel\OctoSlider\Components;

use \DB;
use Validator;
use Cms\Classes\ComponentBase;
use October\Rain\Support\ValidationException;
use OctoDevel\OctoSlider\Models\Item as SliderItem;

class Simple extends ComponentBase
{
    public $simpleItems;

    public function componentDetails()
    {
        return [
            'name'        => 'Simple Item',
            'description' => 'Display a selected octoslider item. You can use any jQuery slideshow plugin you want.'
        ];
    }

    public function defineProperties()
    {
        return [
            'idSlide' => [
                'title'        => 'Slider',
                'description'  => 'Choose the slider item that will display.',
                'type'         => 'dropdown',
                'default'      => '',
            ]
        ];
    }

    public function getIdSlideOptions()
    {
        $slides = SliderItem::all();

        $array_dropdown = ['0'=>'- select one - '];

        foreach ($slides as $slide)
        {
            $array_dropdown[$slide->id] = $slide->title;
        }

        return $array_dropdown;
    }

    public function onRun()
    {
        // Getting register
        $sliderItem = new SliderItem();
        $item = $sliderItem->where('id', '=', $this->propertyOrParam('idSlide'))->first();
        $this->simpleItems = $this->page['simpleItems'] = $item;
    }
}