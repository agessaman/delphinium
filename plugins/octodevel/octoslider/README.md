# OctoSlider Plugin

Create a slideshow in your website using jquery slider plugins.

## How do this work

The plugin provides several components for building jQuery slideshows plugins. Currently available plugins: Nivo Slider and Camera Slider.

### Camera Slider Component

Use the `octosliderCamera` component to display the Camera slideshow jQuery plugin. The component has the following properties:

* **idSlide** - Choose an item previously registered to use their images to slide.
* **jqueryIncludeCamera** - Include the jQuery library version 1.7.1 required by Camera jQuery plugin in your theme. The default value is **No**
* **jqueryMigrateIncludeCamera** - Include the jQuery migrate library in your theme. Enable this option if the plugin is not working with your jQury library. The default value is **No**
* **skinCamera** - Choose the skin the do you like to use with Camera Plugin.  The default value is **Camera azure**
* **navigationCamera** - Display the navigation button (prev, next and play/stop buttons), if no they will be always hidden. The default value is **No**
* **loaderCamera** - Display a loader status. The default value is **Pie**
* **loaderColorCamera** - Set a hexdecimal color to the loader. The default value is **#eeeeee**
* **loaderBgColorCamera** - Set a hexdecimal background color to the loader. The default value is **#222222**
* **loaderOpacityCamera** - Set the opacity to the loader (accpted values are: 0, .1, .2, .3, .4, .5, .6, .7, .8, .9, 1). The default value is **.8**
* **loaderPaddingCamera** - Set how many empty pixels you want to display between the loader and its background. The default value is **2**
* **loaderStrokeCamera** - Set the thickness both of the pie loader and of the bar loader. Remember: for the pie, the loader thickness must be less than a half of the pie diameter. The default value is **7**
* **barPositionCamera** - Choose the loader bar position. The default value is **Bottom**
* **barDirectionCamera** - Choose the loader bar direction if your "Display loader" choice was "Bar". The default value is **Left to right**
* **hoverCamera** - Pause on state hover. Not available for mobile devices. The default value is **Yes**
* **thumbnailsCamera** - Display thumbnails from the slider items image. The default value is **Yes**
* **playPauseCamera** - Display play/pause buttons control. The default value is **No**
* **paginationCamera** - Display slider pagination. The default value is **Yes**
* **captionEffectCamera** - Choose what effect the image captions will have. The default value is **Fade from bottom**

The `octosliderCamera` component injects the following variables to the page where it's used:

* **cameraItems** - a list of octoslider items loaded from the database.
* **skinCamera** - contains the value of the `skinCamera` component's property.
* **navigationCamera** - contains the value of the `navigationCamera` component's property.
* **loaderCamera** - contains the value of the `loaderCamera` component's property.
* **loaderColorCamera** - contains the value of the `loaderColorCamera` component's property.
* **loaderBgColorCamera** - contains the value of the `loaderBgColorCamera` component's property.
* **loaderOpacityCamera** - contains the value of the `loaderOpacityCamera` component's property.
* **loaderPaddingCamera** - contains the value of the `loaderPaddingCamera` component's property.
* **loaderStrokeCamera** - contains the value of the `loaderStrokeCamera` component's property.
* **barPositionCamera** - contains the value of the `barPositionCamera` component's property.
* **barDirectionCamera** - contains the value of the `barDirectionCamera` component's property.
* **hoverCamera** - contains the value of the `hoverCamera` component's property.
* **thumbnailsCamera** - contains the value of the `thumbnailsCamera` component's property.
* **playPauseCamera** - contains the value of the `playPauseCamera` component's property.
* **paginationCamera** - contains the value of the `paginationCamera` component's property.
* **captionEffectCamera** - contains the value of the `captionEffectCamera` component's property.


The next example shows the basic `octosliderCamera` component usage:

    title = "Home"
    url = "/"

    [octosliderCamera]
    idSlide = "1"
    jqueryIncludeCamera = "no"
    jqueryMigrateIncludeCamera = "no"
    skinCamera = "camera_azure_skin"
    navigationCamera = "false"
    loaderCamera = "pie"
    loaderColorCamera = "#eeeeee"
    loaderBgColorCamera = "#222222"
    loaderOpacityCamera = ".8"
    loaderPaddingCamera = "2"
    loaderStrokeCamera = "7"
    barPositionCamera = "bottom"
    barDirectionCamera = "leftToRight"
    hoverCamera = "true"
    thumbnailsCamera = "true"
    playPauseCamera = "false"
    paginationCamera = "true"
    captionEffectCamera = "fadeFromBottom"
    ==
    <!-- Camera Slideshow -->
    <div class="container">
        {% component 'octosliderCamera' %}
    </div>
    <!-- /End Camera Slideshow -->


The `octosliderCamera` component is coded in the default component partial `plugins/octodevel/octoslider/components/camera/default.htm`. If the default markup is not suitable for your website, feel free to copy it from the default partial and replace the `{% component %}` call in the example above with the partial contents.

For more information visit the Camera slider documentation: [http://www.pixedelic.com/plugins/camera](http://www.pixedelic.com/plugins/camera)

### Nivo Slider Component

Use the `octosliderNivo` component to display the Nivo Slider jQuery plugin. The component has the following properties:

* **idSlide** - Choose an item previously registered to use their images to slide.
* **jqueryIncludeNivo** - Include the jQuery library version 1.9 required by Nivo jQuery plugin in your theme. The default value is **No**
* **effectNivo** - Choose the effect for your slides.  The default value is **Random**
* **slicesNivo** - Specify how many slices the animations will have. Only available for slice animations. The default value is **15**
* **boxColsNivo** - Specify how many cols the box animations will have. Only available for box animations. The default value is **8**
* **boxRowsNivo** - Specify how many rows the box animations will have. Only available for box animations. The default value is **#eeeeee**
* **animSpeedNivo** - Set the animation speed in milliseconds. The default value is **500**
* **pauseTimeNivo** - Set how long each slide will show in milliseconds. The default value is **3000**
* **startSlideNivo** - Set starting slide (0 index). The default value is **0**
* **directionNavNivo** - Display Next & Prev navigation. The default value is **Yes**
* **controlNavNivo** - Display 1,2,3... navigation.. The default value is **Yes**
* **controlNavThumbsNivo** - Display thumbnails if `controlNavNivo` is enabled. The default value is **Yes**
* **pauseOnHoverNivo** - Pause on state hover. Not available for mobile devices. The default value is **Yes**
* **manualAdvanceNivo** - Force manual transitions. The default value is **No**
* **prevTextNivo** - The `Prev` direction navigation text. The default value is **Prev**
* **nextTextNivo** - The `Next` direction navigation text. The default value is **Next**
* **randomStartNivo** - Set to start on a random slide. The default value is **No**
* **themeNivo** - Set the theme that the Nivo Slider will use. The default value is **No**

The `octosliderNivo` component injects the following variables to the page where it's used:

* **nivoItems** - a list of octoslider items loaded from the database.
* **effectNivo** - contains the value of the `effectNivo` component's property.
* **slicesNivo** - contains the value of the `slicesNivo` component's property.
* **boxColsNivo** - contains the value of the `boxColsNivo` component's property.
* **boxRowsNivo** - contains the value of the `boxRowsNivo` component's property.
* **animSpeedNivo** - contains the value of the `animSpeedNivo` component's property.
* **pauseTimeNivo** - contains the value of the `pauseTimeNivo` component's property.
* **startSlideNivo** - contains the value of the `startSlideNivo` component's property.
* **directionNavNivo** - contains the value of the `directionNavNivo` component's property.
* **controlNavNivo** - contains the value of the `controlNavNivo` component's property.
* **controlNavThumbsNivo** - contains the value of the `controlNavThumbsNivo` component's property.
* **pauseOnHoverNivo** - contains the value of the `pauseOnHoverNivo` component's property.
* **manualAdvanceNivo** - contains the value of the `manualAdvanceNivo` component's property.
* **prevTextNivo** - contains the value of the `prevTextNivo` component's property.
* **nextTextNivo** - contains the value of the `nextTextNivo` component's property.
* **randomStartNivo** - contains the value of the `randomStartNivo` component's property.
* **themeNivo** - contains the value of the `themeNivo` component's property.

The next example shows the basic `octosliderNivo` component usage:

    title = "Home"
    url = "/"

    [octosliderNivo]
    idSlide = "1"
    jqueryIncludeNivo = "no"
    effectNivo = "random"
    slicesNivo = "15"
    boxColsNivo = "8"
    boxRowsNivo = "4"
    animSpeedNivo = "500"
    pauseTimeNivo = "3000"
    startSlideNivo = "0"
    directionNavNivo = "true"
    controlNavNivo = "true"
    controlNavThumbsNivo = "false"
    pauseOnHoverNivo = "true"
    manualAdvanceNivo = "false"
    prevTextNivo = "Prev"
    nextTextNivo = "Next"
    randomStartNivo = "false"
    themeNivo = "none"
    ==
    <!-- Nivo Slider -->
    <div class="container">
        {% component 'octosliderNivo' %}
    </div>
    <!-- /End Nivo Slider -->


The `octosliderNivo` component is coded in the default component partial `plugins/octodevel/octoslider/components/nivo/default.htm`. If the default markup is not suitable for your website, feel free to copy it from the default partial and replace the `{% component %}` call in the example above with the partial contents.

For more information visit the Nivo slider documentation: [http://docs.dev7studios.com/jquery-plugins/nivo-slider](http://docs.dev7studios.com/jquery-plugins/nivo-slider)

### Simple Item Component

Use the `octosliderSimple` component to display the slider item. The component has the following properties:

* **idSlide** - Choose an item previously registered to use their images to slide.

The `octosliderSimple` component injects the following variables to the page where it's used:

* **nivoItems** - a list of octoslider items loaded from the database.

The next example shows the basic `octosliderNivo` component usage:

    title = "Home"
    url = "/"

    [octosliderSimple]
    idSlide = "1"
    ==
    <!-- My Own Slider -->
    <div class="container">
        {% component 'octosliderSimple' %}
    </div>
    <!-- /End My Own Slider -->


The `octosliderSimple` component is coded in the default component partial `plugins/octodevel/octoslider/components/simple/default.htm`. If the default markup is not suitable for your website, feel free to copy it from the default partial and replace the `{% component %}` call in the example above with the partial contents.