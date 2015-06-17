<?php  namespace Delphinium\Iris\Components;

use Delphinium\Iris\Models\Home as IrisCharts;
use Delphinium\Iris\Classes\Iris as IrisClass;
use Delphinium\Blackberry\Models\Developers as LtiConfigurations;
use Cms\Classes\ComponentBase;

/**
 * Description of IrisLegend
 *
 * @author Delphinium
 */
class IrisLegend extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Iris Legend',
            'description' => 'Legend for Sunburst Chart'
        ];
    }
    
    public function onRun()
    {	
        $this->addCss('/plugins/delphinium/iris/assets/css/irislegend.css');
        $options = $this->getFontSizeOptions();
        $val = $this->property('fontSize');
        $val>=sizeof($options)?$val=1:$val=$val;
        $this->page['fontSize'] = $options[$val];
    }
    
    public function defineProperties()
    {
        return [
            'fontSize' => [
                'title'        => 'Font Size',
                'description'  => 'Choose a font size to be used in the legend',
                'type'         => 'dropdown',
                'default'      => '1',
            ]
            
        ];
    }
    
    public function getFontSizeOptions()
    {
        for($i=0;$i<=4;$i++)
        { 
            $start = 8;
            $array_dropdown[$i] = $start+($i*2);
        }
        
        return $array_dropdown;
    }
}

