<?php namespace OctoDevel\OctoSlider\Components;

use \DB;
use Validator;
use Cms\Classes\ComponentBase;
use October\Rain\Support\ValidationException;
use OctoDevel\OctoSlider\Models\Item as SliderItem;

class Camera extends ComponentBase
{
    public $cameraItems;
    public $jqueryIncludeCamera;
    public $jqueryMigrateIncludeCamera;
    public $skinCamera;
    public $navigationCamera;
    public $loaderCamera;
    public $loaderColorCamera;
    public $loaderBgColorCamera;
    public $loaderOpacityCamera;
    public $loaderPaddingCamera;
    public $loaderStrokeCamera;
    public $barPositionCamera;
    public $barDirectionCamera;
    public $hoverCamera;
    public $thumbnailsCamera;
    public $playPauseCamera;
    public $paginationCamera;
    public $captionEffectCamera;

    public function componentDetails()
    {
        return [
            'name'        => 'Camera jQuery Plugin',
            'description' => 'Display a slideshow from a selected octoslider item using Camera free jQuery plugin by Pixedelic.'
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
            ],
            'jqueryIncludeCamera' => [
                'title'        => 'Include jQuery',
                'description'  => 'If enabled the plugin will include jQuery library v1.7.1 in your theme. Requirement: jQuery library 1.4 or higher.',
                'type'         => 'dropdown',
                'default'      => 'no',
                'group'        => 'Libraries',
            ],
            'jqueryMigrateIncludeCamera' => [
                'title'        => 'Include jQuery migrate?',
                'description'  => 'If the plugin is not working with your library, enable this option. The Camera jQuery Plugin have some incompatibilities with new jQuery versions.',
                'type'         => 'dropdown',
                'default'      => 'no',
                'group'        => 'Libraries',
            ],
            'skinCamera' => [
                'title'        => 'Camera skin',
                'description'  => 'Choose the skin the do you like to use with Camera Plugin.',
                'type'         => 'dropdown',
                'default'      => 'camera_azure_skin',
                'group'        => 'Options',
            ],
            'navigationCamera' => [
                'title'        => 'Display navigation',
                'description'  => 'If enabled the navigation button (prev, next and play/stop buttons) will be visible, if false they will be always hidden.',
                'type'         => 'dropdown',
                'default'      => 'false',
                'group'        => 'Options',
            ],
            'hoverCamera' => [
                'title'       => 'Pause on hover',
                'description' => 'Pause on state hover. Not available for mobile devices.',
                'type'        => 'dropdown',
                'default'     => 'true',
                'group'       => 'Options',
            ],
            'thumbnailsCamera' => [
                'title'       => 'Display thumbnails',
                'description' => 'Display thumbnails from the slider items image.',
                'type'        => 'dropdown',
                'default'     => 'true',
                'group'       => 'Options',
            ],
            'playPauseCamera' => [
                'title'       => 'Display play/pause buttons',
                'description' => 'Display play/pause buttons control.',
                'type'        => 'dropdown',
                'default'     => 'false',
                'group'       => 'Options',
            ],
            'paginationCamera' => [
                'title'       => 'Display pagination',
                'description' => 'Display slider pagination.',
                'type'        => 'dropdown',
                'default'     => 'true',
                'group'       => 'Options',
            ],
            'captionEffectCamera' => [
                'title'       => 'Image caption effect',
                'description' => 'Choose what effect the image captions will have.',
                'type'        => 'dropdown',
                'default'     => 'fadeFromBottom',
                'group'       => 'Options',
            ],
            'loaderCamera' => [
                'title'       => 'Display loader',
                'description' => 'Display a loader status.',
                'type'        => 'dropdown',
                'default'     => 'pie',
                'group'       => 'Loader',
            ],
            'loaderColorCamera' => [
                'title'       => 'Loader color',
                'description' => 'Use a hexadecimal web color.',
                'type'        => 'string',
                'default'     => '#eeeeee',
                'group'       => 'Loader',
            ],
            'loaderBgColorCamera' => [
                'title'       => 'Loader background color',
                'description' => 'Use a hexadecimal web color.',
                'type'        => 'string',
                'default'     => '#222222',
                'group'       => 'Loader',
            ],
            'loaderOpacityCamera' => [
                'title'       => 'Loader opacity',
                'description' => 'Accpted values are: 0, .1, .2, .3, .4, .5, .6, .7, .8, .9, 1',
                'type'        => 'string',
                'validationPattern' => '^(\.[1-9]|0|1)$',
                'validationMessage' => 'Invalid format. Accpted values are: 0, .1, .2, .3, .4, .5, .6, .7, .8, .9, 1',
                'default'     => '.8',
                'group'       => 'Loader',
            ],
            'loaderPaddingCamera' => [
                'title'       => 'Loader padding',
                'description' => 'How many empty pixels you want to display between the loader and its background.',
                'type'        => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Invalid format.',
                'default'     => '2',
                'group'       => 'Loader',
            ],
            'loaderStrokeCamera' => [
                'title'       => 'Loader stroke',
                'description' => 'The thickness both of the pie loader and of the bar loader. Remember: for the pie, the loader thickness must be less than a half of the pie diameter.',
                'type'        => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Invalid format.',
                'default'     => '7',
                'group'       => 'Loader',
            ],
            'barPositionCamera' => [
                'title'       => 'Loader bar position',
                'description' => 'Choose the loader bar position.',
                'type'        => 'dropdown',
                'default'     => 'bottom',
                'group'       => 'Loader',
            ],
            'barDirectionCamera' => [
                'title'       => 'Loader bar direction',
                'description' => 'Choose the loader bar direction if your "Display loader" choice was "Bar".',
                'type'        => 'dropdown',
                'default'     => 'leftToRight',
                'group'       => 'Loader',
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

    public function getJqueryIncludeCameraOptions()
    {
        return ['no' => 'No', 'yes' => 'Yes'];
    }

    public function getJqueryMigrateIncludeCameraOptions()
    {
        return ['no' => 'No', 'yes' => 'Yes'];
    }

    public function getSkinCameraOptions()
    {
        return [
            'camera_custom_skin'    => 'My Custom Skin',
            'camera_amber_skin'     => 'Camera amber',
            'camera_ash_skin'       => 'Camera ash',
            'camera_azure_skin'     => 'Camera azure',
            'camera_beige_skin'     => 'Camera beige',
            'camera_black_skin'     => 'Camera black',
            'camera_blue_skin'      => 'Camera blue',
            'camera_brown_skin'     => 'Camera brown',
            'camera_burgundy_skin'  => 'Camera burgundy',
            'camera_charcoal_skin'  => 'Camera charcoal',
            'camera_chocolate_skin' => 'Camera chocolate',
            'camera_coffee_skin'    => 'Camera coffee',
            'camera_cyan_skin'      => 'Camera cyan',
            'camera_fuchsia_skin'   => 'Camera fuchsia',
            'camera_gold_skin'      => 'Camera gold',
            'camera_green_skin'     => 'Camera green',
            'camera_grey_skin'      => 'Camera grey',
            'camera_indigo_skin'    => 'Camera indigo',
            'camera_khaki_skin'     => 'Camera khaki',
            'camera_lime_skin'      => 'Camera lime',
            'camera_magenta_skin'   => 'Camera magenta',
            'camera_maroon_skin'    => 'Camera maroon',
            'camera_orange_skin'    => 'Camera orange',
            'camera_olive_skin'     => 'Camera olive',
            'camera_pink_skin'      => 'Camera pink',
            'camera_pistachio_skin' => 'Camera pistachio',
            'camera_pink_skin'      => 'Camera pink',
            'camera_red_skin'       => 'Camera red',
            'camera_tangerine_skin' => 'Camera tangerine',
            'camera_turquoise_skin' => 'Camera turquoise',
            'camera_violet_skin'    => 'Camera violet',
            'camera_white_skin'     => 'Camera white',
            'camera_yellow_skin'    => 'Camera yellow',
        ];
    }

    public function getNavigationCameraOptions()
    {
        return ['false' => 'No', 'true' => 'Yes'];
    }

    public function getLoaderCameraOptions()
    {
        return ['pie' => 'Pie', 'bar' => 'Bar', 'none' => 'None'];
    }

    public function getHoverCameraOptions()
    {
        return ['false' => 'No', 'true' => 'Yes'];
    }

    public function getThumbnailsCameraOptions()
    {
        return ['false' => 'No', 'true' => 'Yes'];
    }

    public function getPlayPauseCameraOptions()
    {
        return ['false' => 'No', 'true' => 'Yes'];
    }

    public function getPaginationCameraOptions()
    {
        return ['false' => 'No', 'true' => 'Yes'];
    }

    public function getBarDirectionCameraOptions()
    {
        return ['leftToRight' => 'Left to right', 'rightToLeft' => 'Right to left', 'topToBottom' => 'Top to bottom', 'bottomToTop' => 'Bottom to top'];
    }

    public function getBarPositionCameraOptions()
    {
        return ['left' => 'Left', 'right' => 'Right', 'top' => 'Top', 'bottom' => 'Bottom'];
    }

    public function getCaptionEffectCameraOptions()
    {
        return ['moveFromLeft' => 'Move from left', 'moveFomRight' => 'Move from right', 'moveFromTop' => 'Move from top', 'moveFromBottom' => 'Move from bottom', 'fadeIn' => 'Fade in', 'fadeFromLeft' => 'Fade from left', 'fadeFromRight' => 'Fade from right', 'fadeFromTop' => 'Fade from top', 'fadeFromBottom' => 'Fade from bottom'];
    }

    public function onRun()
    {
        // Getting register
        $sliderItem = new SliderItem();
        $item = $sliderItem->where('id', '=', $this->propertyOrParam('idSlide'))->first();
        $this->cameraItems = $this->page['cameraItems'] = $item;

        // Setting parameters
        $this->jqueryIncludeCamera = $this->page['jqueryIncludeCamera'] = $this->propertyOrParam('jqueryIncludeCamera');
        $this->jqueryMigrateIncludeCamera = $this->page['jqueryMigrateIncludeCamera'] = $this->propertyOrParam('jqueryMigrateIncludeCamera');
        $this->skinCamera = $this->page['skinCamera'] = $this->propertyOrParam('skinCamera');
        $this->navigationCamera = $this->page['navigationCamera'] = $this->propertyOrParam('navigationCamera');
        $this->loaderCamera = $this->page['loaderCamera'] = $this->propertyOrParam('loaderCamera');
        $this->loaderColorCamera = $this->page['loaderColorCamera'] = $this->propertyOrParam('loaderColorCamera');
        $this->loaderBgColorCamera = $this->page['loaderBgColorCamera'] = $this->propertyOrParam('loaderBgColorCamera');
        $this->loaderOpacityCamera = $this->page['loaderOpacityCamera'] = $this->propertyOrParam('loaderOpacityCamera');
        $this->loaderPaddingCamera = $this->page['loaderPaddingCamera'] = $this->propertyOrParam('loaderPaddingCamera');
        $this->loaderStrokeCamera = $this->page['loaderStrokeCamera'] = $this->propertyOrParam('loaderStrokeCamera');
        $this->barPositionCamera = $this->page['barPositionCamera'] = $this->propertyOrParam('barPositionCamera');
        $this->barDirectionCamera = $this->page['barDirectionCamera'] = $this->propertyOrParam('barDirectionCamera');
        $this->hoverCamera = $this->page['hoverCamera'] = $this->propertyOrParam('hoverCamera');
        $this->thumbnailsCamera = $this->page['thumbnailsCamera'] = $this->propertyOrParam('thumbnailsCamera');
        $this->playPauseCamera = $this->page['playPauseCamera'] = $this->propertyOrParam('playPauseCamera');
        $this->paginationCamera = $this->page['paginationCamera'] = $this->propertyOrParam('paginationCamera');
        $this->captionEffectCamera = $this->page['captionEffectCamera'] = $this->propertyOrParam('captionEffectCamera');

        // Add vendors
        $this->addCss('assets/vendor/pixedelic/camera/css/camera.css');

        // Include jQuery
        if($this->jqueryIncludeCamera == 'yes')
            $this->addJs('assets/js/jquery-1.7.1.min.js');

        // Include jQuery Migration
        if($this->jqueryMigrateIncludeCamera == 'yes')
            $this->addJs('assets/js/jquery-migrate-1.2.1.min.js');

        $this->addJs('assets/vendor/pixedelic/camera/scripts/jquery.mobile.customized.min.js');
        $this->addJs('assets/vendor/pixedelic/camera/scripts/jquery.easing.1.3.js');
        $this->addJs('assets/vendor/pixedelic/camera/scripts/camera.min.js');
    }
}