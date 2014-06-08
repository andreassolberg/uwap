<?php


/**
 * Persistent Storage. Pluggable.
 */
abstract class So_Storage {
	function __construct() {
	}
	public abstract function getClient($client_id);
}
