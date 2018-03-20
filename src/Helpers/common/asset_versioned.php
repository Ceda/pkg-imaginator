<?php

function asset_versioned($path)
{
	if (!\File::exists(public_path($path))) {
		return url($path);
	}

	return url($path) . '?' . \File::lastModified(public_path($path));
}