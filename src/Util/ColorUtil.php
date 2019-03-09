<?php
namespace Oka\FileBundle\Util;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
final class ColorUtil
{
	public static function rgbToHex($rgb)
	{
		// Convert to Hex
		$hex = '';
		
		if (null !== $rgb) {
			foreach ($rgb as $value) {
				$char = [];
				
				// Find first character in the chunk
				$char[1] = floor($value / 16);
				
				// Now find the second character in the chunk
				$char[2] = floor($value % 16);
				
				foreach($char as $base_10) {
					$hex .= strval(base_convert($base_10, 10, 16));
				}
			}
		}
		// They gave us null, so return black...
		else {
			$hex = '000000';
		}
		
		return $hex;
	}
}
