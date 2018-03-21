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

* Before using the Imaginator there are a few steps we need to do for it to run smoothly.
* First things first we need to run `php artisan migrate` in order to build our database structure.
* If you wish to modify the database structure in any way or form simply create update migrations.
* After building the proper database structure we need to define the schemas for `Imaginator Templates` and `Imaginator Variations`.
* This can be achieved by running the `php artisan vendor:publish --tag=imaginator-configs` command.
* Now if you go to your config folder you'll find the `imaginator` folder in there with two files in it.
* One will be `app.php` and the other is `schemas.php`.
* As you might have guessed, you define these so called `Imaginator Templates` and `Imaginator Variations` in the `schemas.php` file.
* I will show you an example of how a properly defined `Imaginator Template` with `Imaginator Variations` should look like.

```
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
 ```
 
 * After you successfully set up all the templates and variations you need, go to the backend of your project and use the `php artisan imaginator:refresh` command to generate the defined templates and variations.
 * The above mentioned example will generate two variations if and one template if we go by the default config. `Picture` and `Picture - retina`. If we were to set the hasTranslations to true, this would change, generating four variations with the `( locale )` suffixes.
 * If you make any modifications in the `schemas.php` file in the future, you'll need to run the `php artisan imaginator:refresh` command to regenerate the templates nad variations.
 * Running this command will not overwrite existing variations, it will only edit them, delete or add new ones. It's completely safe to run it over and over without any modifications. We do so in the deploy process to ensure all the Schemas are properly built on all our environments. 
 * Now you have properly setup your database and you're ready to go the next step.
 * All that's left is to include the assets in your project.
 * To do just that, we'll need to run the `php artisan vendor:publish --tag=imaginator-assets` command.
 * This will generate an `assets` folder in the root of your project (if you don't have one already) and place the Imaginator assets in the `imaginator` folder in it.
 * For the Imaginator to work, you'll need to include the accompanying css styles into your template.
 * The key files are `libs-imaginator.css` (located in project_root/assets/imaginator/dist/css) and `libs-imaginator.js` (located in project_root/assets/imaginator/dist/js).
 * Only a few more steps left to go!
 * If you so desire, you can use the included example Imaginator that is bound to an input, all you have to do is include the `imaginator-input.js` file (located in project_root/assets/imagiantor/dist/js).
 * However for the `imaginator-input.js` to work properly, you need to define the `ImaginatorCreateUrl` global variable in js.
 * You can do so by including this code snippet into your project:
 
 ```
     ...
     <script>
         window.ImaginatorCreateUrl = '{{ route_raw('imaginator.create') }}';
     </script>
     ...
 
 ```
 
 * After you created the global variable, you can just create an input and by clicking on it running the Imaginator.
 * The Imaginator input has to have some required parameters so far. I'll include an example right here
 
 ```
    ...
    <input type="text" <!-- Input type must be text or number, either should be fine -->
           name="photo" <!-- Name the input whatever you need to -->
           readOnly="readOnly" <!-- For the sake of convenience and safety set the input to be readOnly either through JS or HTML as you see in this example -->
           data-imaginator <!--  !!REQUIRED!! Set the Imaginator to init on this input -->
           data-imaginator-template="gallery" <!-- !!REQUIRED!! Define the template, Imaginator should use to create the Images, this is the Imaginator Template name you defined in schemas -->
     >
    ...
    
 ```
 
 * Now after clicking on the input you should see the Imaginator popping up. Now you are ready to use the Imaginator however you like!
 * CREATE SOMETHING AWESOME!
 
    
## Contributing

**Please note these few steps while contributing**

* The src/assets/dist folder should always be commited to git.
* Before a commit always compile assets with the `gulp prod` command while in the src/_frontend folder.
* The final assets should be minified without sourcemaps to achieve the best results.

