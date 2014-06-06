<?php


class FeedItem extends StoredModel {
	
	protected static $collection = 'feed';
	protected static $primaryKey = '_id';
	protected static $mongoID = true;
	protected static $validProps = array(
		'title',
		'message',
		'links',
		
		'class',

		'ts', 'oid',

		'id',

		'location', 'files', 'dtstart', 'signup', 'status', 'promoted',

		'audience',
		'inresponseto',

		'uwap-userid',
		'uwap-clientid',
	);


	public $responses = array();
	public $lastActivity;

	public $user;
	public $client;


	public function getTS() {
		if ($this->has('ts')) return $this->get('ts');
		if ($this->has('updated')) return $this->get('updated');
		if ($this->has('created')) return $this->get('created');
		return null;
	}

	public static function filterAudience($audience, $user, $client) {

		$memberGroups = array();
		if (isset($user)) {
			$memberGroups = $user->getGroups();
			$memberGroups = array_keys($memberGroups);
		} else if (isset($client)) {
			$memberGroups = $client->get('groups');
		}



		if (isset($audience['groups'])) {
			$ng = array();
			foreach($audience['groups'] AS $group) {
				if (in_array($group, $memberGroups)) {
					$ng[] = $group;
				}
			}
			$audience['groups'] = $ng;
		}

		return $audience;

	}

	public function calculateLastActivity() {

		// echo "\n-----\nCalculating lastActivity for this item " . $this->get('message', '...') . "\n";

		$ts = $this->getTS();

		// echo "Top " . $ts . "\n";

		foreach($this->responses AS $response) {
			$r = $response->getTS();
			if ($ts === null) {
				// echo "Response " . $r . " overrides " . $ts . "\n";
				$ts = $r;
			} else if ($r > $ts) {
				// echo "Response " . $r . " overrides " . $ts . "\n";
				$ts = $r;
			}

		}
		// echo "Finally set to " . $ts . "\n";
		$this->lastActivity = $ts;
	}

	public function hasClass($class) {
		if (empty($this->properties['class'])) return false;
		if (!is_array($this->properties['class'])) return false;
		foreach($this->properties['class'] AS $c) {
			if ($class === $c) return true;
		}
		return false;
	}


	public function getSummaryVerb() {
		$str = '';
		if ($this->hasClass('message')) {
			$str .= 'posted a message';

		} else if ($this->hasClass('article')) {
			$str .= 'posted an article';
		} else if ($this->hasClass('file')) {
			$str .= 'uploaded a file';
		} else if ($this->hasClass('media')) {
			$str .= 'uploaded media content';
		} else if ($this->hasClass('event')) {
			$str .= 'posted an event';
		} else if ($this->hasClass('response')) {
			$str .= 'responded to an event';
		} else if ($this->hasClass('comment')) {
			$str .= 'replied with a comment';
		} else {
			$str .= 'posted some content';
		}
		return $str;
	}

	public function getSummarySubject() {

		if ($this->user) {
			return $this->user->get('name');
		}
		if ($this->client) {
			return $this->client->get('name');
		}
		return 'Someone';
	}



	public function getSummaryGroups() {

		// print_r($this); exit;

		if (!$this->has('audience')) return '';
		$audience = $this->get('audience');
		if (!isset($audience['groups'])) return '';

		if (count($audience['groups']) === 1) {
			return 'to ' . $audience['groups'][0];
		} else {
			return 'to multiple groups';
		}
	}

	public function getSummary() {


		$subject = $this->getSummarySubject();
		$verb = $this->getSummaryVerb();
		$target = $this->getSummaryGroups();

		return $subject . ' ' . $verb  . ' ' . $target;

	} 


	public static function generate($properties, $user, $client) {

		$userproperties = array(
			'title', 'message', 'links', 'class', 'audience', 'inresponseto',
			'ts', 'oid', 
			'promoted', 'signup',
			'location', 'files', 'dtstart', 'status',
		);

		foreach($properties AS $k => $v) {
			if (!in_array($k, $userproperties)) {
				unset($properties[$k]);
			}
		}

		if ($user !== null) {
			$properties['uwap-userid'] = $user->get('userid');
		}
		if ($client !== null) {
			$properties['uwap-clientid'] = $client->get('id');
		}

		if (isset($properties['audience'])) {

			$properties['audience'] = self::filterAudience($properties['audience'], $user, $client);

		}

		if (isset($properties['oid'])) {
			$item = self::getByKey('oid', $properties['oid']);

			// echo "We are looking up oid " . $properties['oid'] . " and found"; print_r($item); exit;

			if (!empty($item)) return $item;
		} else {
			// echo "About to insert. did not find an oid with " . $properties['oid']; exit;
		}

		// TODO: Validate inresponseto and permissions to that object?
		
		$feedItem = new FeedItem($properties);

		// print_r($feedItem);

		$feedItem->store();

		// echo "About to return a new client: ";

		return $feedItem;
	}


	public function update($properties, $user, $client) {

		$userproperties = array(
			'title', 'message', 'links', 'class', 'audience', 'inresponseto',
			'ts', 'oid', 
			'promoted', 'signup',
			'location', 'files', 'dtstart', 'status',
		);

		foreach($properties AS $k => $v) {
			if (!in_array($k, $userproperties)) {
				unset($properties[$k]);
			}
		}

		if ($user !== null) {
			$properties['uwap-userid'] = $user->get('userid');
		}
		if ($client !== null) {
			$properties['uwap-clientid'] = $client->get('id');
		}

		if (isset($properties['audience'])) {
			$properties['audience'] = self::filterAudience($properties['audience'], $user, $client);
		}

		foreach($properties AS $k => $v) {
			$this->set($k, $v);
		}
		$fieldsToSave = array_keys($properties);
		$this->store($fieldsToSave);

		return $this;

	}


	public function getJSON($opts = array()) {

		$ret = parent::getJSON($opts);
		$ret['lastActivity'] = $this->lastActivity;
		return $ret;
	}


	public function __construct($properties) {

		// if (!$user instanceof User) throw new Exception('Creating new role without a proper User object');
		// if (!$group instanceof Group) throw new Exception('Creating new role without a proper Group object');

		if (isset($properties['_id'])) {
			$properties['id'] = $properties['_id']->{'$id'};
			unset($properties['_id']);
		}

		parent::__construct($properties);

		if (isset($properties['uwap-userid'])) {
			$this->user = User::getById($properties['uwap-userid']);
		}
		if (isset($properties['uwap-clientid'])) {
			$this->client = Client::getById($properties['uwap-clientid']);
		}

	}



	
}