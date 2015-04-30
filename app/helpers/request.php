<?php namespace helpers;

/**
 * Request validation
 * @author Bobi <me@borislazarov.com> on 1 Nov 2014
 */
class Request {

	/**
	 * Verify request
	 * @author Bobi <me@borislazarov.com> on 1 Nov 2014
	 * @param  string $name    Name of varable
	 * @param  string $type    The type of varable
	 * @param  mixed  $default Default retun value
	 * @return mixed
	 */
	public static function get($name, $type, $default = NULL) {

		if (isset($_POST[$name])) {
			$var = $_POST[$name];
		} elseif (isset($_GET[$name])) {
			$var = $_GET[$name];
		} else {
			$var = NULL;
		}

		switch ($type) {
			case 'string':
				if (is_string($var)) {
					$validated = $var;
				} else {
					$validated = $default;
				}
				break;
			
			case 'integer':
				if (filter_var($var, FILTER_VALIDATE_INT)) {
					$validated = $var;
				} else {
					$validated = $default;
				}
				break;

			case 'float':
				if (filter_var($var, FILTER_VALIDATE_FLOAT)) {
					$validated = $var;
				} else {
					$validated = $default;
				}
				break;

			case 'boolean':
				if (filter_var($var, FILTER_VALIDATE_BOOLEAN)) {
					$validated = $var;
				} else {
					$validated = $default;
				}
				break;

			case 'array':
				if (is_array($var)) {
					$validated = $var;
				} else {
					$validated = $default;
				}
				break;
		}

		return $validated;

	}

}