<?php

return [
  [
    /*
    * Imaginator template info
    */
    'name' => 'gallery',
    'label' => 'Gallery',
    'description' => null,
    /*
    * Imaginator variations
    */
    'variations' => [ //acts as a wrapper around all variations, all variations have to be defined in it
      /*
      * One Imaginator variation
      */
      [
        'name' => 'Picture',
        'breakpoint' => 't',
        'density' => 'regular',
        'locale' => 'all',
        'quality' => 80,
        'width' => 1920,
        'height' => 768,
        'hasRetina' => true,
        'hasTranslation' => false,
      ],
    ],
  ],
];