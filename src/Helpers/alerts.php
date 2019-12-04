<?php

function push_warning($value)
{
  push_flash('warnings', $value);
  return false;
}

function push_error($value)
{
  push_flash('errors-custom', $value);
  return false;
}

function push_success($value)
{
  push_flash('successes', $value);
  return true;
}

function push_info($value)
{
  push_flash('infos', $value);
  return true;
}

function push_flash($key, $value)
{
  $values = \Illuminate\Support\Facades\Session::get($key, []);
  $values[] = $value;
  \Illuminate\Support\Facades\Session::flash($key, $values);
}
