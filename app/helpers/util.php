<?php namespace helpers;

/**
 * Util Class
 * @author Bobi <me@borislazarov.com> on 5 Oct 2014
 */
class Util {

	/**
	 * Function for short description
	 * @author Bobi <me@borislazarov.com> on 3 Oct 2014
	 * @param  string 	$string     Text for limitation
	 * @param  integer 	$word_limit Words limit
	 * @return string             	Short description
	 */
	public static function limitWords($string, $word_limit) {
		$words = explode(" ",$string);
		return implode(" ",array_splice($words,0,$word_limit));
	}

	/**
	 * Get your domain name
	 * @author Bobi <me@borislazarov.com> on 27 Oct 2014
	 * @return string exmaple.com
	 */
	public static function site() {

		$site = str_replace("http://", "", DIR);
		$site = str_replace("/", "", $site);

		return $site;

	}
	
	/**
	 * Message for modal
	 * @author Bobi <me@borislazarov.com> on 18 Nov 2014
	 * @return json
	 */
	public static function modal($status, $title = NULL, $body = NULL, $type = "display") {
		$data = json_encode(
		    [
			"status" => $status,
			"title"  => $title,
			"body"   => $body
		    ]
		);
		
		switch($type) {
			case "display":
				echo $data;
				break;
			case "json":
				return $data;
		}
	}
	
	/**
	 * Generate page number for cache (It`s used in fetchAllVideos() function)
	 * @author Bobi <me@borislazarov.com> on 12 Oct 2014
	 * @param  string $limit SQL limit string
	 * @return integer
	 */
	public static function generatePageNumber($limit) {

		$tmpPage = $limit['start'] / $limit['limit'];

		return $tmpPage;
	}
	
	/**
	 * @author user644783 on 4 Mar 2011
	 * @source http://stackoverflow.com/questions/1993721/how-to-convert-camelcase-to-camel-case
	 */
	public static function decamelize($word) {
		return preg_replace('/(^|[a-z])([A-Z])/e', 'strtolower(strlen("\\1") ? "\\1_\\2" : "\\2")', $word); 
	}
	
	/**
	 * @author user644783 on 4 Mar 2011
	 * @source http://stackoverflow.com/questions/1993721/how-to-convert-camelcase-to-camel-case
	 */
	public static function camelize($word) { 
		return preg_replace('/(^|_)([a-z])/e', 'strtoupper("\\2")', $word); 
	}
	
	public static function dbug($var) {
		echo '<pre>'; // This is for correct handling of newlines
		ob_start();
		var_dump($var);
		$a = ob_get_contents();
		ob_end_clean();
		echo htmlspecialchars($a, ENT_QUOTES); // Escape every HTML special chars (especially > and < )
		echo '</pre>';
	}
	
	/**
	* @link http://gist.github.com/385876
	*/
	function csv_to_array($filename='', $delimiter=',')
	{
	    if(!file_exists($filename) || !is_readable($filename))
		return FALSE;
	
	    $header = NULL;
	    $data = array();
	    if (($handle = fopen($filename, 'r')) !== FALSE)
	    {
		while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
		{
		    if(!$header)
			$header = $row;
		    else
			$data[] = array_combine($header, $row);
		}
		fclose($handle);
	    }
	    return $data;
	}

    /**
     * Check if given string is JSON
     *
     * @param $string
     * @return bool
     */
    public static function isJSON($string){
        return is_string($string) && is_object(json_decode($string)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

}