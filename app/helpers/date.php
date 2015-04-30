<?php namespace helpers;
use \DateTime;

/**
 * Date Time Class
 * @author Bobi <me@borislazarov.com> on 28 Oct 2014
 */
class Date {

    /**
    * A sweet interval formatting, will use the two biggest interval parts.
    * On small intervals, you get minutes and seconds.
    * On big intervals, you get months and days.
    * Only the two biggest parts are used.
    *
    * @param DateTime $start
    * @param DateTime|null $end
    * @return string
    */
    public static function dateDiff ($start, $end = NULL, $expdande = false) {

	if(!($start instanceof DateTime)) {
	    $start = new DateTime($start);
	}
       
	if($end === NULL) {
	    $end = new DateTime();
	}
       
	if(!($end instanceof DateTime)) {
	    $end = new DateTime($start);
	}
       
	$interval = $end->diff($start);
	
	$format = array();
	if($interval->y !== 0) {
	    $time     =  $interval->y == 1 ? _T("year") :  _T("years");
	    $format[] = "%y " . $time;
	}
	if($interval->m !== 0) {
	    $time     =  $interval->m == 1 ? _T("month") :  _T("months");
	    $format[] = "%m " . $time;
	}
	if($interval->d !== 0) {
	    $time     =  $interval->d == 1 ? _T("day") :  _T("days");
	    $format[] = "%d " . $time;
	}
	if($interval->h !== 0) {
	    $time     =  $interval->h == 1 ? _T("hour") :  _T("hours");
	    $format[] = "%h " . $time;
	}
	if($interval->i !== 0) {
	    $time     =  $interval->i == 1 ? _T("minute") :  _T("minutes");
	    $format[] = "%i " . $time;
	}
	if($interval->s !== 0) {
	    if(!count($format)) {
		return _T("по-малко от минута");
	    } else {
		$time     =  $interval->s == 1 ? _T("second") :  _T("seconds");
		$format[] = "%s ". $time;
	    }
	}
       
	// We use the two biggest parts
	if ($expdande == true) {
	    if(count($format) > 1) {
		$format = array_shift($format). _T(" and ") .array_shift($format);
	    } else {
		$format = array_pop($format);
	    }
	}
       
	$format = array_shift($format);

	// Prepend 'since ' or whatever you like
	return $interval->format($format);

    }

    /**
     * Format date
     * @author Bobi <me@borislazarov.com> on 10 Oct 2014
     * @param  string $date Date
     * @param  string $type Format
     * @return string
     */
    public static function dateFormat($date, $type) {

	$dt = new DateTime($date);

	switch ($type) {
	    case 'readable':
		$formatedDate = $dt->format('d/m/Y H:i');
		break;

	    case 'timestamp':
		$formatedDate = $dt->getTimestamp();
		break;
	    
	    case 'hours':
		$formatedDate = $dt->format('H:i');
		break;
	    
	    default:
		$formatedDate = $date;
		break;
	}

	return $formatedDate;

    }

}