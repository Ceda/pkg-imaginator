<?php

return [
  'max_quality' => env('IMAGINATOR_COMPRESSION_MAX_QUALITY', 80),
  'min_quality' => env('IMAGINATOR_COMPRESSION_MIN_QUALITY', 60),
  'valid_pngquant_locations' => [
    '/usr/bin/pngquant',
    '/usr/local/bin/pngquant'
  ],
  'compress_png' => env('IMAGINATOR_COMPRESS_PNG', true),
];