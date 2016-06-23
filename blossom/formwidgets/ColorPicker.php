<?php

namespace Delphinium\Blossom\FormWidgets;

use Backend\Classes\FormWidgetBase;

/*************************************
 * ColorPicker Form Widget
 * Renders a color picker field
 * Used by Backend & Frontend Forms
 * Customize as needed !!! availableColor
 * Accessible to all Blossom components
 *************************************/
class ColorPicker extends FormWidgetBase
{
	/**
     * @var array Default available colors
     */
    public $availableColors = [
        '#4d7123', '#004715',
        '#b2ba31', '#7aa238',
        '#687722', '#c2a224',
        '#aa701d', '#775106', 
        '#b85920', '#e7cf29',
        '#e6a52d', '#ffcd30',
		'#5c707c', '#95a1aa', 
		'#c3e5dd', '#e7e7d5', 
        '#efefef', '#888888', '#333333',
    ];// plus custom color
    /**
	https://www.uvu.edu/web/standards/
		#4d7123,#b2ba31,
		#004715,#e9d66e,
		#c2a224,#e7e7d5,
		#efe2c7,#ffcd30,
		#e7cf29,#7aa238,
		#b1a980,#cab67c,
		#e6a52d,
		#687722,#aa701d,
		#95a1aa,#bbb3a5,
		#775106,#5c707c,
		#877f70,#c3e5dd,
		#b85920,#f4e889
	
	 **********
     * {@inheritDoc}
     */
    protected $defaultAlias = 'delphinium_blossom_color_picker';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
		$this->fillFromConfig([
            'availableColors',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('colorpicker');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
		$this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $value = $this->getLoadValue();
        $this->vars['availableColors'] = $this->availableColors;
        $this->vars['isCustomColor'] = !in_array($value, $this->availableColors);
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
		$this->addCss('vendor/colpick/css/colpick.css', 'delphinium.blossom');
		$this->addJs('vendor/colpick/js/colpick.js', 'delphinium.blossom');
		$this->addCss('css/colorpicker.css', 'delphinium.blossom');
		$this->addJs('js/colorpicker.js', 'delphinium.blossom');
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        return strlen($value) ? $value : null;
    }

}
