<?php

if ( ! function_exists('image_url'))
{
	/**
	 * Asset image helper.
	 *
	 * @param  string  $image
	 * @return string
	 */
	function image_url($image)
	{
		return Assets::image($image);
	}
}
