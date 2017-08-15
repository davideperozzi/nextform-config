<?php

namespace Nextform\Config;

class Signature
{
    /**
     * @param AbstractConfig $config
     * @return string
     */
    public static function get(AbstractConfig $config)
    {
        $fields = $config->getFields();
        $hashes = [
            $fields->getRoot()->getSignature()
        ];

        foreach ($fields as $field) {
            $hashes[] = $field->getSignature();
        }

        return md5(implode($hashes));
    }
}
