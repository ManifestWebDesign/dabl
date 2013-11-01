<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

class StringFormat {

	/**
	 * @param string $string
	 * @return string
	 */
	static function className($string) {
		return self::titleCase($string);
	}

	/**
	 * @param string $string
	 * @return string
	 */
	static function classMethod($string) {
		return lcfirst(self::titleCase($string));
	}

	/**
	 * @param string $string
	 * @return string
	 */
	static function pluralClassMethod($string) {
		return lcfirst(self::plural(self::titleCase($string)));
	}

	/**
	 * @param string $string
	 * @return string
	 */
	static function classProperty($string) {
		return lcfirst(self::titleCase($string));
	}

	/**
	 * @param string $string
	 * @return string
	 */
	static function pluralClassProperty($string) {
		return lcfirst(self::plural(self::titleCase($string)));
	}

	/**
	 * Returns non-pluralized version of string, with words separated by dashes.
	 * @param string $string
	 * @return string
	 */
	static function url($string) {
		return str_replace('_', '-', self::variable($string));
	}

	/**
	 * Returns pluralized version of string, with words separated by dashes.
	 * @param string $string
	 * @return string
	 */
	static function pluralURL($string) {
		return str_replace('_', '-', self::pluralVariable($string));
	}

	/**
	 * Returns non-pluralized version of string, with words separated by underscores.
	 * @param string $string
	 * @return string
	 */
	static function variable($string) {
		return implode('_', self::getWords($string, true));
	}

	/**
	 * Returns non-pluralized version of string, with upper-case
	 * words separated by underscores.
	 * @param string $string
	 * @return string
	 */
	static function constant($string) {
		return strtoupper(self::variable($string));
	}

	/**
	 * Returns pluralized version of string, with words separated by underscores.
	 * Intended for variable names.
	 * @param string $string
	 * @return string
	 */
	static function pluralVariable($string) {
		return self::plural(implode('_', self::getWords($string, true)));
	}

	/**
	 * Converts a given string to title case
	 * @param string $string
	 * @return string
	 */
	static function titleCase($string, $delimiter = '') {
		return implode($delimiter, array_map('ucfirst', self::getWords($string, true)));
	}

	/**
	 * Returns array of the words in a string
	 * @param string $string
	 * @return array
	 */
	static function getWords($string, $lowercase = false) {

		$string = self::clean($string, ' ');

		// before an upper and a lower. FBar -> F Bar
		$string = preg_replace('/(.)([A-Z])([a-z])/', '$1 $2$3', $string);

		// between a lower and an upper. fooB -> foo B
		$string = preg_replace('/([a-z])([A-Z])/', '$1 $2', $string);

		// after any sequence of numbers.  45times -> 45 times
		$string = preg_replace('/([0-9])([^0-9])/', '$1 $2', $string);

		if ($lowercase) {
			$string = strtolower($string);
		}

		do {
			$string = str_replace('  ', ' ', $string, $count);
		} while ($count > 0);

		return explode(' ', $string);
	}

	/**
	 * Returns the plural version of the given word.  If the plural version is
	 * the same, then this method will simply add an 's' to the end of
	 * the word.
	 * @param string $string
	 * @return string
	 */
	static function plural($string) {
		$plural = array(
			array('/(quiz)$/i', "$1zes"),
			array('/^(ox)$/i', "$1en"),
			array('/([m|l])ouse$/i', "$1ice"),
			array('/(matr|vert|ind)ix|ex$/i', "$1ices"),
			array('/(x|ch|ss|sh)$/i', "$1es"),
			array('/([^aeiouy]|qu)y$/i', "$1ies"),
			array('/([^aeiouy]|qu)ies$/i', "$1y"),
			array('/(hive|move)$/i', "$1s"),
			array('/(?:([^f])fe|([lr])f)$/i', "$1$2ves"),
			array('/sis$/i', "ses"),
			array('/([ti])um$/i', "$1a"),
			array('/(buffal|tomat)o$/i', "$1oes"),
			array('/(bu)s$/i', "$1ses"),
			array('/(alias|status|campus)$/i', "$1es"),
			array('/(octop|cact|vir)us$/i', "$1i"),
			array('/(ax|test)is$/i', "$1es"),
			array('/^(m|wom)an$/i', "$1en"),
			array('/(child)$/i', "$1ren"),
			array('/(p)erson$/i', "$1eople"),
			array('/s$/i', "s"),
			array('/$/', "s")
		);

		$words = self::getWords($string);
		$word = array_pop($words);

		// check for matches using regular expressions
		foreach ($plural as &$pattern) {
			if (preg_match($pattern[0], $word)) {
				$prefix = substr($string, 0, strrpos($string, $word));
				return $prefix . preg_replace($pattern[0], $pattern[1], $word);
			}
		}
		return $string . 's';
	}

	/**
	 * Replaces accent characters with non-accent characters
	 * @param string $str
	 * @return string
	 */
	static function removeAccents($str) {
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		return str_replace($a, $b, $str);
	}

	static function clean($str, $delimiter = ' ') {
		$str = self::removeAccents($str);

		// remove apostrophes and don't use them to separate words
		$str = preg_replace("/([a-zA-Z])'([a-zA-Z])/", '$1$2', $str);

		// treat all characters that aren't numbers and letters as word separators
		$str = preg_replace('/[^a-zA-Z0-9]/', ' ', $str);

		// remove trailing or leading dashes
		$str = trim($str, ' ');

		// remove any occurances of double dashes
		do {
			$str = str_replace('  ', ' ', $str, $count);
		} while ($count > 0);

		// replace dashes with delimiter if delimiter is not a dash
		if ($delimiter !== ' ') {
			$str = str_replace(' ', $delimiter, $str);
		}
		return $str;
	}

}