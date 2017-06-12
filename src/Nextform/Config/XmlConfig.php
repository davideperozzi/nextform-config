<?php

namespace Nextform\Config;

class XmlConfig extends AutoConfig
{
	/**
	 * @var string
	 */
	protected static $extension = 'xml';

	/**
	 * @param string $input
	 * @param boolean $treatAsContent
	 */
	public function __construct($input, $treatAsContent = false) {
		if (false == $treatAsContent) {
			parent::__construct($input, $treatAsContent);
		}
		else {
			$this->setParser(static::$extension, $input);
		}
	}
}