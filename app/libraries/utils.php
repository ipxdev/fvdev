<?php

class Utils
{
	public static function isRegistered()
	{
		return Auth::check() && Auth::user()->registered;
	}

	public static function isConfirmed()
	{
		return Auth::check() && Auth::user()->confirmed;
	}

	public static function isProd()
	{
		return App::environment() == ENV_PRODUCTION;
	}	

	public static function isNinja()
	{
		return self::isNinjaProd() || self::isNinjaDev();
	}

	public static function isNinjaProd()
	{
		return isset($_ENV['NINJA_PROD']) && $_ENV['NINJA_PROD'];		
	}

	public static function isNinjaDev()
	{
		return isset($_ENV['NINJA_DEV']) && $_ENV['NINJA_DEV'];
	}

	public static function isPro()
	{
		return Auth::check() && Auth::user()->isPro();
	}

	public static function isAdmin()
	{
		return Auth::check() && Auth::user()->isAdmin();
	}


	public static function getUserType()
	{
		if (Utils::isNinja()) {
			return USER_TYPE_CLOUD_HOST;
		} else {
			return USER_TYPE_SELF_HOST;
		}
	}

	public static function getDemoAccountId()
	{
		return isset($_ENV[DEMO_ACCOUNT_ID]) ? $_ENV[DEMO_ACCOUNT_ID] : false;
	}

	public static function isDemo()
	{
		return Auth::check() && Auth::user()->isDemo();
	}
        
	public static function getNewsFeedResponse($userType = false) 
	{
		if (!$userType) {
			$userType = Utils::getUserType();
		}

		$response = new stdClass;
		$response->message = isset($_ENV["{$userType}_MESSAGE"]) ? $_ENV["{$userType}_MESSAGE"] : '';
		$response->id = isset($_ENV["{$userType}_ID"]) ? $_ENV["{$userType}_ID"] : '';
		$response->version = NINJA_VERSION;
	
		return $response;
	}

	public static function getProLabel($feature)
	{
		if (Auth::check() 
				&& !Auth::user()->isPro() 
				&& $feature == ACCOUNT_ADVANCED_SETTINGS)
		{
			return '&nbsp;<sup class="pro-label">PRO</sup>';
		}
		else
		{
			return '';
		}
	}

	public static function basePath() 
	{
		return substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1);
	}

	public static function trans($input)
	{
		$data = [];

		foreach ($input as $field)
		{
			if ($field == "checkbox")
			{
				$data[] = $field;
			}
			else
			{
				$data[] = trans("texts.$field");
			}
		}

		return $data;
	}
	
	public static function fatalError($message = false, $exception = false)
	{
		if (!$message)
		{
			$message = "Ha ocurrido un error, por favor intente de nuevo.";
		}

		static::logError($message . ' ' . $exception);		

		$data = [		
			'showBreadcrumbs' => false,
			'hideHeader' => true
		];

		return View::make('error', $data)->with('error', $message);
	}

	public static function logError($error, $context = 'PHP')
	{
		$count = Session::get('error_count', 0);
		Session::put('error_count', ++$count);
		if ($count > 100) return 'logged';

		$data = [
			'context' => $context,
			'user_id' => Auth::check() ? Auth::user()->id : 0,
			'user_name' => Auth::check() ? Auth::user()->getDisplayName() : '',
			'url' => Input::get('url', Request::url()),
			'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
			'ip' => Request::getClientIp(),
			'count' => Session::get('error_count', 0)
		];

		Log::error($error."\n", $data);

		/*
		Mail::queue('emails.error', ['message'=>$error.' '.json_encode($data)], function($message)
		{			
			$message->to($email)->subject($subject);
		});		
		*/
	}

	public static function parseFloat($value)
	{
		$value = preg_replace('/[^0-9\.\-]/', '', $value);
		return floatval($value);
	}

	public static function formatPhoneNumber($phoneNumber) 
	{
	    $phoneNumber = preg_replace('/[^0-9a-zA-Z]/','',$phoneNumber);

	    if (!$phoneNumber) {
	    	return '';
	    }

	    if(strlen($phoneNumber) > 10) {
	        $countryCode = substr($phoneNumber, 0, strlen($phoneNumber)-10);
	        $areaCode = substr($phoneNumber, -10, 3);
	        $nextThree = substr($phoneNumber, -7, 3);
	        $lastFour = substr($phoneNumber, -4, 4);

	        $phoneNumber = '+'.$countryCode.' ('.$areaCode.') '.$nextThree.'-'.$lastFour;
	    }
	    else if(strlen($phoneNumber) == 10 && in_array(substr($phoneNumber, 0, 3), array(653, 656, 658, 659))) {
	        /**
	         * SG country code are 653, 656, 658, 659
	         * US area code consist of 650, 651 and 657
	         * @see http://en.wikipedia.org/wiki/Telephone_numbers_in_Singapore#Numbering_plan
	         * @see http://www.bennetyee.org/ucsd-pages/area.html
	         */
	        $countryCode = substr($phoneNumber, 0, 2);
	        $nextFour = substr($phoneNumber, 2, 4);
	        $lastFour = substr($phoneNumber, 6, 4);

	        $phoneNumber = '+'.$countryCode.' '.$nextFour.' '.$lastFour;
	    }
	    else if(strlen($phoneNumber) == 10) {
	        $areaCode = substr($phoneNumber, 0, 3);
	        $nextThree = substr($phoneNumber, 3, 3);
	        $lastFour = substr($phoneNumber, 6, 4);

	        $phoneNumber = '('.$areaCode.') '.$nextThree.'-'.$lastFour;
	    }
	    else if(strlen($phoneNumber) == 7) {
	        $nextThree = substr($phoneNumber, 0, 3);
	        $lastFour = substr($phoneNumber, 3, 4);

	        $phoneNumber = $nextThree.'-'.$lastFour;
	    }

	    return $phoneNumber;
	}

	public static function formatMoney($value, $currencyId = false)
	{
		if (!$currencyId)
		{
			$currencyId = Session::get(SESSION_CURRENCY);
		}

		$currency = Currency::remember(DEFAULT_QUERY_CACHE)->find($currencyId);		

		if (!$currency) 
		{
			$currency = Currency::remember(DEFAULT_QUERY_CACHE)->find(1);		
		}
		
		return $currency->symbol .' '. number_format($value, $currency->precision, $currency->decimal_separator, $currency->thousand_separator);
	}

	public static function pluralize($string, $count) 
	{
		$field = $count == 1 ? $string : $string . 's';		
		$string = trans("texts.$field", ['count' => $count]);		
		return $string;
	}

	public static function toArray($data)
	{
		return json_decode(json_encode((array) $data), true);
	}

	public static function toSpaceCase($camelStr)
	{
		return preg_replace('/([a-z])([A-Z])/s','$1 $2', $camelStr);
	}

	public static function timestampToDateTimeString($timestamp) {
		$timezone = Session::get(SESSION_TIMEZONE, DEFAULT_TIMEZONE);
		$format = Session::get(SESSION_DATETIME_FORMAT, DEFAULT_DATETIME_FORMAT);
		return Utils::timestampToString($timestamp, $timezone, $format);		
	}

	public static function timestampToDateString($timestamp) {
		$timezone = Session::get(SESSION_TIMEZONE, DEFAULT_TIMEZONE);
		$format = Session::get(SESSION_DATE_FORMAT, DEFAULT_DATE_FORMAT);
		return Utils::timestampToString($timestamp, $timezone, $format);
	}

	public static function dateToString($date) {		
		$dateTime = new DateTime($date); 		
		$timestamp = $dateTime->getTimestamp();
		$format = Session::get(SESSION_DATE_FORMAT, DEFAULT_DATE_FORMAT);
		return Utils::timestampToString($timestamp, false, $format);
	}

	public static function timestampToString($timestamp, $timezone = false, $format)
	{
		if (!$timestamp) {
			return '';
		}		
		$date = Carbon::createFromTimeStamp($timestamp);
		// if ($timezone) {
		// 	$date->tz = $timezone;	
		// }
		// if ($date->year < 1900) {
		// 	return '';
		// }
		// return $date->format($format);

		$datef = strtotime($date);

		$year = date("Y", $datef); 
		$month = date("m", $datef); 
		$day = date("d", $datef);

		if($month=='1'){$new_month = 'Ene';}
	    if($month=='2'){$new_month = 'Feb';}
	    if($month=='3'){$new_month = 'Mar';}
	    if($month=='4'){$new_month = 'Abr';}
	    if($month=='5'){$new_month = 'May';}
	    if($month=='6'){$new_month = 'Jun';}
	    if($month=='7'){$new_month = 'Jul';}
	    if($month=='8'){$new_month = 'Ago';}
	    if($month=='9'){$new_month = 'Sep';}
	    if($month=='10'){$new_month = 'Oct';}
	    if($month=='11'){$new_month = 'Nov';}
	    if($month=='12'){$new_month = 'Dic';}	

	    $new_date = $day." ".$new_month." ".$year;
	    return $new_date;
	}	

	public static function toSqlDate($date, $formatResult = true)
	{
		if (!$date)
		{
			return null;
		}

		$timezone = Session::get(SESSION_TIMEZONE);
		$format = Session::get(SESSION_DATE_FORMAT);


		$dateTime = DateTime::createFromFormat($format, $date, new DateTimeZone($timezone));
		return $formatResult ? $dateTime->format('Y-m-d') : $dateTime;
		return $date;
	}
	
	public static function fromSqlDate($date, $formatResult = true)
	{
		if (!$date || $date == '0000-00-00')
		{
			return '';
		}
		
		// $timezone = Session::get(SESSION_TIMEZONE);
		// $format = Session::get(SESSION_DATE_FORMAT);

		// $dateTime = DateTime::createFromFormat('Y-m-d', $date, new DateTimeZone($timezone));
		// return $formatResult ? $dateTime->format($format) : $dateTime;

		$datef = strtotime($date);

		$year = date("Y", $datef); 
		$month = date("m", $datef); 
		$day = date("d", $datef);

		if($month=='1'){$new_month = 'Ene';}
	    if($month=='2'){$new_month = 'Feb';}
	    if($month=='3'){$new_month = 'Mar';}
	    if($month=='4'){$new_month = 'Abr';}
	    if($month=='5'){$new_month = 'May';}
	    if($month=='6'){$new_month = 'Jun';}
	    if($month=='7'){$new_month = 'Jul';}
	    if($month=='8'){$new_month = 'Ago';}
	    if($month=='9'){$new_month = 'Sep';}
	    if($month=='10'){$new_month = 'Oct';}
	    if($month=='11'){$new_month = 'Nov';}
	    if($month=='12'){$new_month = 'Dic';}	

	    $new_date = $day." ".$new_month." ".$year;
	    return $new_date;
	}

	public static function fromSqlDate2($date, $formatResult = true)
	{	
		if (!$date || $date == '0000-00-00')
		{
			return '';
		}
		

		$datef = strtotime($date);

		$year = date("Y", $datef); 
		$month = date("m", $datef); 
		$day = date("d", $datef);

		if($month=='1'){$new_month = 'Ene';}
	    if($month=='2'){$new_month = 'Feb';}
	    if($month=='3'){$new_month = 'Mar';}
	    if($month=='4'){$new_month = 'Abr';}
	    if($month=='5'){$new_month = 'May';}
	    if($month=='6'){$new_month = 'Jun';}
	    if($month=='7'){$new_month = 'Jul';}
	    if($month=='8'){$new_month = 'Ago';}
	    if($month=='9'){$new_month = 'Sep';}
	    if($month=='10'){$new_month = 'Oct';}
	    if($month=='11'){$new_month = 'Nov';}
	    if($month=='12'){$new_month = 'Dic';}	

	    $new_date = $day." ".$new_month." ".$year;
	    return $new_date;
	}

	public static function today($formatResult = true)
	{	
		$timezone = Session::get(SESSION_TIMEZONE);
		$format = Session::get(SESSION_DATE_FORMAT);
		$date = date_create(null, new DateTimeZone($timezone));

		if ($formatResult) 
		{
			return $date->format($format);
		}
		else
		{
			return $date;
		}
	}

	public static function trackViewed($name, $type, $url = false)
	{
		if (!$url)
		{
			$url = Request::url();
		}
		
		$viewed = Session::get(RECENTLY_VIEWED);	
		
		if (!$viewed)
		{
			$viewed = [];
		}

		$object = new stdClass;
		$object->url = $url;
		$object->name = ucwords($type) . ': ' . $name;
	
		$data = [];

		for ($i=0; $i<count($viewed); $i++)
		{
			$item = $viewed[$i];
			
			if ($object->url == $item->url || $object->name == $item->name)
			{
				continue;				
			}	

			array_unshift($data, $item);		
		}

		array_unshift($data, $object);
			
		if (count($data) > RECENTLY_VIEWED_LIMIT)
		{
			array_pop($data);
		}

		Session::put(RECENTLY_VIEWED, $data);
	}

	public static function processVariables($str)
	{
		if (!$str) {
			return '';
		}

		$variables = ['MONTH', 'QUARTER', 'YEAR'];
		for ($i=0; $i<count($variables); $i++)
		{
			$variable = $variables[$i];
			$regExp = '/:' . $variable . '[+-]?[\d]*/';
			preg_match_all($regExp, $str, $matches);
			$matches = $matches[0];
			if (count($matches) == 0) {
				continue;
			}
			foreach ($matches as $match) {
				$offset = 0;
				$addArray = explode('+', $match);
				$minArray = explode('-', $match);
				if (count($addArray) > 1) {
					$offset = intval($addArray[1]);
				} else if (count($minArray) > 1) {
					$offset = intval($minArray[1]) * -1;
				}				

				$val = Utils::getDatePart($variable, $offset);
				$str = str_replace($match, $val, $str);
			}
		}

		return $str;
	}

	private static function getDatePart($part, $offset)
	{
		$offset = intval($offset);
		if ($part == 'MONTH') {
			return Utils::getMonth($offset);
		} else if ($part == 'QUARTER') {
			return Utils::getQuarter($offset);
		} else if ($part == 'YEAR') {
			return Utils::getYear($offset);
		}
	}

	private static function getMonth($offset)
	{
		$months = [ "January", "February", "March", "April", "May", "June",
			"July", "August", "September", "October", "November", "December" ];

		$month = intval(date('n')) - 1;

		$month += $offset;
		$month = $month % 12;

		if ($month < 0)
		{
			$month += 12;
		}
		
		return $months[$month];
	}

	private static function getQuarter($offset)
	{
		$month = intval(date('n')) - 1;
		$quarter = floor(($month + 3) / 3);
		$quarter += $offset;
    	$quarter = $quarter % 4;
    	if ($quarter == 0) {
         	$quarter = 4;   
    	}
    	return 'Q' . $quarter;
	}

	private static function getYear($offset) 
	{
		$year = intval(date('Y'));
		return $year + $offset;
	}

	public static function getEntityName($entityType)
	{
		return ucwords(str_replace('_', ' ', $entityType));
	}

	public static function getClientDisplayName($model)
	{
		if ($model->client_name) 
		{
			return $model->client_name;
		}
	}

	public static function encodeActivity($person = null, $action, $entity = null, $otherPerson = null)
	{
		$person = $person ? $person->getDisplayName() : '<i>Sistema</i>';
		$entity = $entity ? '[' . $entity->getActivityKey() . ']' : '';
		$otherPerson = $otherPerson ? 'a ' . $otherPerson->getDisplayName() : '';

		$entitynew = ' ';
		$findme   = 'invoice';

		$pos = strpos($entity, $findme);
		if ($pos !== false) 
		{
			
			$entitynew = substr($entity, 8);
			$entitynew = 'la [' .'factura'.$entitynew;
		}
		else
		{
		    $findme   = 'client';
		    $pos = strpos($entity, $findme);
			if ($pos !== false) 
			{
				$entitynew = substr($entity, 7);
				$entitynew = ' al [' .'cliente'.$entitynew;
			}
			else
			{
				$findme   = 'quote';
			    $pos = strpos($entity, $findme);
				if ($pos !== false) 
				{
					$entitynew = substr($entity, 6);
					$entitynew = ' el [' .'recibo'.$entitynew;
				}
				else
				{
					$findme   = 'payment';
				    $pos = strpos($entity, $findme);
					if ($pos !== false) 
					{
						$entitynew = substr($entity, 8);
						$entitynew = ' el [' .'pago'.$entitynew;
					}
					else
					{
						$findme   = 'credit';
					    $pos = strpos($entity, $findme);
						if ($pos !== false) 
						{
							$entitynew = substr($entity, 7);
							$entitynew = ' el [' .'crédito'.$entitynew;
						}
						else
						{
							$entitynew = $entity;
						}
					}
				}
			}

		}

		return trim("$person $action $entitynew $otherPerson");
	}
	
	public static function decodeActivity($message)
	{
		$pattern = '/\[([\w]*):([\d]*):(.*)\]/i';
		preg_match($pattern, $message, $matches);

		if (count($matches) > 0)
		{
			$type2='';
			$match = $matches[0];
			$type = $matches[1];
			$publicId = $matches[2];
			$name = $matches[3];

			if($type=='factura')
			{
				$type2='invoices';
			}
			if($type=='cliente')
			{
				$type2='clients';
			}
			if($type=='recibo')
			{
				$type2='quotes';
			}

			$link = link_to($type2 . '/' . $publicId, $name);
			$message = str_replace($match, "$type $link", $message);
		}

		return $message;
	}

	public static function generateLicense() {
		$parts = [];
		for ($i=0; $i<5; $i++) {
			$parts[] = strtoupper(str_random(4));
		}
		return join('-', $parts);
	}

	public static function lookupEventId($eventName) 
	{
		if ($eventName == 'create_client') {
			return EVENT_CREATE_CLIENT;
		} else if ($eventName == 'create_invoice') {
			return EVENT_CREATE_INVOICE;
		} else if ($eventName == 'create_quote') {
			return EVENT_CREATE_QUOTE;
		} else if ($eventName == 'create_payment') {
			return EVENT_CREATE_PAYMENT;
		} else {
			return false;
		}
	}

	public static function notifyZapier($subscription, $data) {
    $curl = curl_init();

		$jsonEncodedData = json_encode($data->toJson());
		$opts = [
	    CURLOPT_URL => $subscription->target_url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_CUSTOMREQUEST => 'POST',
	    CURLOPT_POST => 1,
	    CURLOPT_POSTFIELDS => $jsonEncodedData,
	    CURLOPT_HTTPHEADER  => ['Content-Type: application/json', 'Content-Length: ' . strlen($jsonEncodedData)]
		];

    curl_setopt_array($curl, $opts);
    
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if ($status == 410)
    {
    	$subscription->delete();
    }
	}

	public static function remapPublicIds($data) {
    foreach ($data as $index => $record) {
    	if (!isset($data[$index]['public_id'])) {
    		continue;
    	}
      $data[$index]['id'] = $data[$index]['public_id'];
      unset($data[$index]['public_id']);

      foreach ($record as $key => $val) {
      	if (is_array($val)) {      		
      		$data[$index][$key] = Utils::remapPublicIds($val);
      	}
      }
    }
    return $data;
	}

	public static function getApiHeaders($count = 0) {
    return [
      'Content-Type' => 'application/json',
      //'Access-Control-Allow-Origin' => '*',
      //'Access-Control-Allow-Methods' => 'GET',
      //'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Requested-With',      
      //'Access-Control-Allow-Credentials' => 'true',
      'X-Total-Count' => $count,
      //'X-Rate-Limit-Limit' - The number of allowed requests in the current period
      //'X-Rate-Limit-Remaining' - The number of remaining requests in the current period
      //'X-Rate-Limit-Reset' - The number of seconds left in the current period,
    ];
	}	

	public static function startsWith($haystack, $needle)
	{
    return $needle === "" || strpos($haystack, $needle) === 0;
	}

	public static function endsWith($haystack, $needle)
	{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}
	
}