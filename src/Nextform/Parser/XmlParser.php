<?php

namespace Nextform\Parser;

class XmlParser extends AbstractParser
{
    /**
     * @param string $content
     */
    public function __construct($content)
    {
        parent::__construct($content);

        $this->reader = new Reader\XmlReader($this->fieldFactory);
        $this->reader->load($content);
    }
}
