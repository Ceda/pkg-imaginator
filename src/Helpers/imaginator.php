<?php

/**
 * Get imaginator model instance
 *
 * @param int $id
 * @return mixed
 */
function get_imaginator(int $id)
{
  return \Bistroagency\Imaginator\Facades\Imaginator::getImaginator($id);
}

/**
 * Get lazyload object form imaginator id
 *
 * @param int $id
 * @return mixed
 */
function get_imaginator_object(int $id)
{
  return \Bistroagency\Imaginator\Facades\Imaginator::getImaginator($id)->getLazyloadObject(locale());
}

/**
 * Get imaginator by alias or id or create it
 *
 * @param $resources
 * @param string $templateName
 * @param string $anchorPoint
 * @return mixed
 * @deprecated
 */
function get_or_create_imaginator($resources, string $templateName, string $anchorPoint = 'c')
{
  return \Bistroagency\Imaginator\Facades\Imaginator::getOrCreateImaginator($resources, $templateName, $anchorPoint);
}

/**
 * Generate <picture> element from Imaginator
 *
 * @param $imaginator
 * @param string|null $locale
 * @param array $attributes
 * @return mixed
 * @deprecated
 */
function generate_imaginator_picture($imaginator, string $locale = null, array $attributes = [])
{
  return \Bistroagency\Imaginator\Facades\Imaginator::generateImaginatorPicture($imaginator, $locale, $attributes);
}

/**
 * @param array $parameters
 * @param string $glue
 * @return string
 */
function make_imaginator_path(array $parameters, $glue = '/')
{
  return implode($glue, $parameters);
}

if (!function_exists('dummy_image')) {
  /**
   * Create dummy image
   *
   * @param $width
   * @param $height
   * @return string
   */
  function dummy_image($width, $height)
  {
    return route(config('imaginator.app.routes.as') . 'dummy-image', ['width' => $width, 'height' => $height]);
  }
}

/**
 * Returns new imaginator model instance
 *
 * @return
 * @throws \Exception
 */

function get_imaginator_model()
{
  $class = config('imaginator.app.model');

  if (class_exists($class)) {
    return new $class;
  }

  throw new \Exception('Model couldn\'t be located');
}
