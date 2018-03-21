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

