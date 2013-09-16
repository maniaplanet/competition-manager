<?php
/**
 * Emulate some functions
 */
//If gettext is not available
if (!function_exists('_'))
{
	function _($string) {
		return $string;
	}
}
?>