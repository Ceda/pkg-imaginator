<?php

function dummy_image($width, $height)
{
	return route('dummy-image', ['width' => $width, 'height' => $height]);
}