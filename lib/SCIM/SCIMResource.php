<?php


/**
* 
*/
abstract class SCIMResource  {
	
	protected 
		$resourceType = null,
		$created,
		$lastModified,
		$location,
		$version,
		$attributes
		;

	protected static $schemaIDs;


	protected $id;
	protected $externalId;
	protected $schemas = array();

	protected $values = array();

	function __construct($vs) {
		$this->addSchemas(static::$schemaIDs);
		foreach($vs AS $k => $v) {
			error_log ("----");
			try {
				$this->setValue($k, $v);
			} catch(Exception $e) {
				error_log ("Not able to set attribute [" . $k . "] - error:");
				error_log("   › " . $e->getMessage() . "");
			}
		}

	}


	/**
	 * This function parses the Accept-Language http header and returns an associative array with each
	 * language and the score for that language.
	 *
	 * If an language includes a region, then the result will include both the language with the region
	 * and the language without the region.
	 *
	 * The returned array will be in the same order as the input.
	 *
	 * @return An associative array with each language and the score for that language.
	 */
	public static function getAcceptLanguage() {

		if(!array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
			/* No Accept-Language header - return empty set. */
			return array();
		}

		$languages = explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));

		$ret = array();

		foreach($languages as $l) {
			$opts = explode(';', $l);

			$l = trim(array_shift($opts)); /* The language is the first element.*/

			$q = 1.0;

			/* Iterate over all options, and check for the quality option. */
			foreach($opts as $o) {
				$o = explode('=', $o);
				if(count($o) < 2) {
					/* Skip option with no value. */
					continue;
				}

				$name = trim($o[0]);
				$value = trim($o[1]);

				if($name === 'q') {
					$q = (float)$value;
				}
			}

			/* Remove the old key to ensure that the element is added to the end. */
			unset($ret[$l]);

			/* Set the quality in the result. */
			$ret[$l] = $q;

			if(strpos($l, '-')) {
				/* The language includes a region part. */

				/* Extract the language without the region. */
				$l = explode('-', $l);
				$l = $l[0];

				/* Add this language to the result (unless it is defined already). */
				if(!array_key_exists($l, $ret)) {
					$ret[$l] = $q;
				}
			}
		}

		return $ret;
	}

	/**
	 * This function gets the prefered language for the user based on the Accept-Language http header.
	 *
	 * @return The prefered language based on the Accept-Language http header, or NULL if none of the
	 *         languages in the header were available.
	 */
	public static function getHTTPLanguage($availableLanguages) {
		$languageScore = self::getAcceptLanguage();


		/* For now we only use the default language map. We may use a configurable language map
		 * in the future.
		 */
		$languageMap = array('no' => 'nb');

		/* Find the available language with the best score. */
		$bestLanguage = 'nb';
		$bestScore = -1.0;

		// echo "Langauge scrope: ";
		// print_r($languageScore);

		foreach($languageScore as $language => $score) {

			/* Apply the language map to the language code. */
			if(array_key_exists($language, $languageMap)) {
				$language = $languageMap[$language];
			}

			if(!in_array($language, $availableLanguages, TRUE)) {
				/* Skip this language - we don't have it. */
				// echo "Skipping language " . $language . "\n";
				continue;
			}

			/* Some user agents use very limited precicion of the quality value, but order the
			 * elements in descending order. Therefore we rely on the order of the output from
			 * getAcceptLanguage() matching the order of the languages in the header when two
			 * languages have the same quality.
			 */
			// echo "Evaluating [" . $language . "] that has score " . $score . " against current best scor ehiwhc is " . $bestScore . " held by " . $bestLanguage . "\n";
			if($score > $bestScore) {
				$bestLanguage = $language;
				$bestScore = $score;
			}
		}


		// echo "\nAvailable is "; print_r($availableLanguages);

		return $bestLanguage;
	}



	public function validate() {

		$missingAttributes = array();
		$myattr = array_keys($this->values);

		foreach($this->schemas AS $s) {
			$reqattr = $s->getRequiredAttributes();
			$missing = array_diff($reqattr, $myattr);
			if (!empty($missing)) {
				error_log ("[Error] Missing these REQUIRED attributes from schema [" . $s->name . "] : " . join(', ', $missing) . "");
				$missingAttributes = array_merge($missingAttributes, $missing);
			}
		}

		if (!empty($missingAttributes)) {
			throw new Exception('This resource did not include REQUIRED attributes ' . join(', ', $missingAttributes));
		}

	}

	protected function getAttributeDef($name) {
		foreach($this->schemas AS $s) {
			if ($s->hasAttribute($name)) return $s;	
		}
		return null;
	}

	protected function setValue($k,$v) {

		$schema = $this->getAttributeDef($k);		
		if ($schema === null) throw new Exception('Not able to set attribute with name ' . $k . " (not defined in schemas for this resource type)");
		error_log ("Processing attribute [" . $k . "] which is defined in schema [" . $schema->name . "]");
		$schema->validateAttribute($k, $v);
		$this->values[$k] = $v;
	}

	public function get($k) {
		if (isset($this->values[$k])) {
			return $this->values[$k];
		}
		return null;
	}


	protected function addSchemas($schemaids) {

		foreach($schemaids AS $s) $this->addSchema($s);
	}

	protected function addSchema($schemaid) {
		$this->schemas[] = SCIMSchemaDirectory::get($schemaid);
	}

	public function getJSON() {
		$obj = array();
		$x = 0;

		if (isset($this->id)) $obj['id'] = $this->id;
		if (isset($this->externalId)) $obj['externalId'] = $this->externalId;

		foreach($this->values AS $k => $v) {

			$schema = $this->getAttributeDef($k);
			$attrDef = null;
			if ($schema) {
				$attrDef = $schema->getAttributeDef($k);
			}
			
			// If translatable, then translate..
			if ($attrDef && isset($attrDef['translatable']) && $attrDef['translatable'] && is_array($v)) {
				$availableLanguages = array_keys($v);
				$selectedLang = self::getHTTPLanguage($availableLanguages);
				$x = 1;

				// echo "Processing"; print_r($this->values);
				// echo "availableLanguages: "; print_r($availableLanguages);
				// echo "\nselectedLang: "; print_r($selectedLang);
				// echo "\n";

				$obj[$k] = $v[$selectedLang];

			} else {

				$obj[$k] = $v;
					
			}

			
		}

		foreach($this->values AS $k => $v) {
			$schema = $this->getAttributeDef($k);
			$attrDef = null;
			if ($schema) {
				$attrDef = $schema->getAttributeDef($k);
			}
			if ($attrDef && isset($attrDef['overrides'])) {
				$obj[$attrDef['overrides']] = $obj[$k];
				unset($obj[$k]);
			}
		}
		// if ($x === 1) {
		// 	print_r($obj); exit;	
		// }
		

		return $obj;
	}

	public static function getSchemas() {
		return self::$schemaIDs;
	}

}