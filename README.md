# Imaginator #

## Installation

**How to add this package to your app**

Add the following lines to the root of your composer.json:

    ...
    "repositories": [
        {
            "type": "vcs",
            "url": "https://bitbucket.org/bistroagency/pkg-imaginator.git"
        }
    ],
    ...
    
_This step is required to inform our composer.json that we have a foreign repository from which we wish to get additional packages or in our case one package._

After that, simply add the package into your "require" section:

    "bistroagency/pkg-imaginator": "dev-master"

Run composer update

    composer update
 
    
## Usage

**Congratulations! Now you installed Imaginator. What now? You might be asking...**

This is where the fun truly begins.

* First things first we need to run `php artisan migrate` in order to build our database structure.
* If you wish to modify the database structure in any way or form simply create update migrations.
* After building the proper database structure we need to define the schemas for `Imaginator Templates` and `Imaginator Variations`.
* This can be achieved by running the `php artisan vendor:publish --tag=imaginator-configs` command.
* Now if you go to your config folder you'll find the `imaginator` folder in there with two files in it.
* One will be `app.php` and the other is `schemas.php`.
* As you might have guessed, you define these so called `Imaginator Templates` and `Imaginator Variations` in the `schemas.php` file.
* I will show you an example of how a properly define an `Imaginator Template` with `Imaginator Variations` should look like.

~~~~
...
[
    /*
    * Imaginator template info
    */
    'name' => 'gallery', //defines the template name used as a key to access the template
    'label' => 'Gallery', //defines the template label shown in the Imaginator blade
    'description' => null, //you can also describe the functions of this template or just write anything you want
    /*
    * Imaginator variations
    */
    'variations' => [ //acts as a wrapper around all variations, all variations have to be defined in it
        /*
        * One Imaginator variation
        */
        [
            'name' => 'Picture', //defines the name of the variation, sometimes other settings affect it's outcome, it is also used slugified as the folder name
            'breakpoint' => 't', //defines the breakpoint for the json outcome, mostly used for LazyLoad purposes
            'density' => 'regular', //defines the pixel density of the generated image, can be set to 'retina' or 'regular' as of now
            'locale' => 'all', //defines the language of the variation, however the language has to be defined in the imaginator/app.php config file
            'quality' => 80, //defines the generated images quality, in case of a retina variation this is automatically set to 30
            'width' => 1920, //defines the generated images width
            'height' => 768, //defines the generated images height
            'hasRetina' => true, //defines whether a retina variation should be generated alongside the regular one, the retina variation will have the '- retina' suffix added to it's variation name
            'hasTranslation' => false, //defines whether the variation should be translated to all languages defined in the imaginator/app.php config
            //!!WARNING!! if the hasTranslation option is set to true there is no need to define the locale, since it will be ignored, it also adds the '( locale )' suffix to the variation name
        ],
    ],
],
...
~~~~
 
 * After you successfully set up all the templates and variations you need, go to the backend of your project and use the `php artisan imaginator:refresh` command to generate the defined templates and variations.
 * The above mentioned example will generate two variations and one template if we go by the default config. `Picture` and `Picture - retina`. If we were to set the hasTranslations to true, this would change, generating four variations with the `( locale )` suffixes.
 * If you make any modifications in the `schemas.php` file in the future, you'll need to run the `php artisan imaginator:refresh` command to regenerate the templates and variations.
 * Running this command will not overwrite existing variations, it will only edit them, delete or add new ones. It's completely safe to run it over and over without any modifications. We do so in the deploy process to ensure all the Schemas are properly built on all our environments.
 * To do just that, we'll need to run the `php artisan vendor:publish --tag=imaginator-assets` command.
 * This will generate an `assets` folder in the root of your project (if you don't have one already) and place the Imaginator assets in the `imaginator` folder in it.
 * The key files are `libs-imaginator.css` (located in project_root/assets/imaginator/dist/css) and `libs-imaginator.js` (located in project_root/assets/imaginator/dist/js).
 * If you so desire, you can use the included example Imaginator that is bound to an input, all you have to do is include the `imaginator-input.js` file (located in project_root/assets/imagiantor/dist/js).
 * However for the `imaginator-input.js` to work properly, you need to define the `ImaginatorCreateUrl` global variable in js.
 * You can do so by including this code snippet into your project:
 
~~~~
...
<script>
 window.ImaginatorCreateUrl = '{{ route_raw('imaginator.create') }}';
</script>
...
~~~~

 * After you created the global variable, you can just create an input and by clicking on it running the Imaginator.
 * The Imaginator input has to have some required parameters so far. I'll include an example right here
 
~~~~ 
...
<input type="text" <!-- Input type must be text or number, either should be fine -->
       name="photo" <!-- Name the input whatever you need to -->
       readOnly="readOnly" <!-- For the sake of convenience and safety set the input to be readOnly either through JS or HTML as you see in this example -->
       data-imaginator <!--  !!REQUIRED!! Set the Imaginator to init on this input -->
       data-imaginator-template="gallery" <!-- !!REQUIRED!! Define the template, Imaginator should use to create the Images, this is the Imaginator Template name you defined in schemas -->
 >
...
~~~~
 
 * Now after clicking on the input you should see the Imaginator popping up. Now you are ready to use the Imaginator however you like!
 * CREATE SOMETHING AWESOME!
 
 
 ## Getting Images
 
 **Predefined way of getting imaginator Images**
 
 * Load `libs-head.js` file in html head.
 * The first step is to ensure proper functioning on IE 11.
 * Generate picture html markup by calling `generate_imaginator_picture((required) int $id`, `(optional) string $locale`, `(optional) array $attributes)`.
 * You can modify the allowed picture attributes in the `app.php` config file after you published it.
 * To get the LazyLoad json, you can call the `get_imaginator()` helper function.
 * Get lazy load json by calling `get_imaginator((required) int $id)`.
 * To get the `Lazyload Object` you have to execute the `->getLazyloadOjbect()` function which takes `string $locale` as an optional parameter. If you can, always send the locale parameter to the function.
 * Example: `getImaginator(16)->getLazyloadObject()`.
 
 
 ## Configuration
 
 **How to properly configure things**
 
 * You learned the purpose of the `imaginator/schemas.php` file in the **Usage** section.
 * Now to the configuration of the app itself. You'll need to navigate to the `imaginator/app.php` file.
 * It should look like this:
 
~~~~
<?php

return [
  'default_locale' => app()->getLocale(),
  'locales' => [
    'cs' => 'cs',
    'en' => 'en',
  ],
  'model' => \Bistroagency\Imaginator\Models\Imaginator::class,
  'routes' => [
    'prefix' => 'imaginator',
    'as' => 'imaginator.',
    'middlewares' => [
      'web',
    ],
  ],
  'storage' => [
    'tempDestination' => public_path('storage/imaginator/tmp/'),
    'destination' => public_path('storage/imaginator/'),
  ],
  'breakpoints' => [
    't' =>'tiny',
    's' => 'small',
    'm' => 'medium',
    'l' => 'large',
    'xl' => 'xlarge',
    'xxl' => 'xxlarge',
    'fhd' => 'fullhd',
  ],
  'densities' => [
    'regular' => [
      'scale' => 1,
      'suffix' => null,
    ],
    'retina' => [
      'scale' => 2,
      'suffix' => '@2',
    ],
  ],
  'anchor_points' => [
    'tl' => 'top-left',
    't' => 'top',
    'tr' => 'top-right',
    'l' => 'left',
    'c' => 'center',
    'r' => 'right',
    'bl' => 'bottom-left',
    'b' => 'bottom',
    'br' => 'bottom-right',
  ],
];
~~~~

* Here you can basically edit everything about how the Imaginator works.
* The Imaginator package uses these as a reference, to check whether you have properly defined the above mentioned options in the `schemas.php` file.
* Most of this file should be pretty self-explanatory but the `model` setting is the interesting one.
* In the Imaginator package, we wanted to implement an `$imaginator->isUsed()` function to determine, whether the Imaginators are being used somewhere.
* Since each project is different this is a difficult task to implement without creating too many unnecessary database request so we decided to implement a way, to overwrite the native `$imaginator->isUsed()` function, which just returns false as of now.
* It's just as simple as creating a new Model and extending the Imaginator one in the package by it. There you can overwrite the native functions in any way you want.
* But for the package to use your model, you have to edit the `model` setting in the `app.php` file and setting it as the proper class path to your own Imaginator model in your project.
* The keys in the `locales, densities` and `anchor points` settings are used in the `schemas.php` file to generate the templates and variations.
* To access all the setting variables outside of Imaginator, all you have to do is to call the `config()` helper with the `imaginator.` prefix.


## Viewing files

**After all you'd like to see what images you currently have on your page, right?**

As of now there are two ways to display all the 'Images' or as the package calls them 'Imaginators' you either have to go to the route `route('imaginator.index')` or by clicking on an input and choosing the `Přehlad` tab.

**Getting one specific Imaginator**

* To get one specific Imaginator if you know it's ID or Alias call the `get_imaginator((required) $aliasOrId)` function.
* If you want to create or get an Imaginator by it's path use the `get_or_create_imaginator((required [array or string]) $resources, (required) $templateSlug, (optional) $anchorPoint)` function.
* If the Imaginator exists it will return the Imaginator instance if not, it will create a new one and return it. It will use the source files path as an alias for the Imaginator.
* If you pass an array as the first parameter, there are two required keys that the array needs to have. `alias` and `default`.
* **Example:**

~~~~
get_or_create_imaginator([
    'alias' => 'trubadur', //alias under which the imaginator will be saved and retrieved by
    'default' => '/storage/bullshit/krak.jpg', //fallback source to generate variations from
], 'gallery');
~~~~

* If you want to use different sources for different variations, just add the relative path to the file into the array, using the variation slug as the key.
* **Example:**

~~~~
get_or_create_imaginator([
    'alias' => 'trubadur', //alias under which the imaginator will be saved and retrieved by
    'default' => '/storage/images/obraz.jpg', //fallback source to generate variations from
    'picture' => '/storage/images/priatel_ludstva.jpg', //the variation with slug picture will use this image as it's source
    'picture-retina' => '/storage/images/priatel_ludstva@2.jpg', //the variation with slug picture-retina will use this image as it's source
], 'gallery');
~~~~


## Browser support

**All the amazing platforms you can run our Imaginator on**

Mostly all of the modern browser are supported :).

## Package

* [Click here for package version management info](PACKAGE.md)


## Changelog

* [Click here for changelog](CHANGELOG.md)


## Contributing

**Please note these few steps while contributing**

* The src/assets/dist folder should always be commited to git.
* Before a commit always compile assets with the `gulp prod` command while in the src/_frontend folder.
* The final assets should be minified without sourcemaps to achieve the best results.
* Before merge to master make sure to add proper tag to commit! [Read more](PACKAGE.md)

