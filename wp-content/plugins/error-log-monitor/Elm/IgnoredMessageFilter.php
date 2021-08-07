<?php
class Elm_IgnoredMessageFilter extends Elm_LogFilter {
	private $ignoredMessageIndex = array();

	public function __construct(Iterator $iterator, $ignoredMessages) {
		parent::__construct($iterator);
		$this->ignoredMessageIndex = $ignoredMessages;
	}

	/**
	 * Check whether the current element of the iterator is acceptable
	 *
	 * @return bool true if the current element is acceptable, otherwise false.
	 */
	public function accept() {
		$entry = $this->getInnerIterator()->current();
		if ( !isset($entry, $entry['message']) ) {
			return true;
		}

		if ( isset($this->ignoredMessageIndex[$entry['message']]) ) {
			$this->skippedEntryCount++;
			return false;
		} else {
			return true;
		}
	}
}