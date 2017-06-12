<?php

namespace Nextform\Config;

use Nextform\Parser\AbstractParser;
use Nextform\Parser\Exception\LogicException;

class AutoConfig extends AbstractConfig
{
	/**
	 * @var string
	 */
	protected static $extension = '';

	/**
	 * @var AbstractConfig
	 */
	protected $parser = null;

	/**
	 * @var string[]
	 */
	public static $parsers = [
		'xml' => '\Nextform\Parser\XmlParser'
	];

	/**
	 * @param string $input
	 * @param boolean $treatAsContent
	 * @throws LogicException if treat as content is set
	 * @throws LogicException if invalid parser given
	 * @throws LogicException if parser not found
	 */
	public function __construct($input, $treatAsContent = false) {
		if (true == $treatAsContent) {
			// Aborting the process because recognizing
			// the content would be too expensive.
			throw new LogicException(
				'You need to use a specific config if you parse content.'
			);
		}
		else {
			if ( ! is_string($input)) {
				throw new LogicException('Invalid file given');
			}
			else if ( ! file_exists($input)) {
				throw new LogicException(
					sprintf('The file "%s" was not found', $input)
				);
			}

			$extension = strtolower(pathinfo($input, PATHINFO_EXTENSION));

			if (!empty(static::$extension) && $extension != static::$extension) {
				throw new LogicException(
					sprintf(
						'Invalid file extension. "%s" needed. "%s" found',
						static::$extension,
						$extension
					)
				);
			}

			if ( ! array_key_exists($extension, static::$parsers)) {
				throw new LogicException(
					sprintf('Parser for "%s" file not found', $extension)
				);
			}

			$this->setParser($extension, file_get_contents($input));
		}
	}

	/**
	 * @param string $type
	 * @param string $input
	 */
	protected function setParser($type, $input) {
		$this->parser = new static::$parsers[$type]($input);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFields() {
		return $this->parser->getFields();
	}
}