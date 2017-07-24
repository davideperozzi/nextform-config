<?php

namespace Nextform\Fields;

class SelectField extends AbstractField
{
    /**
     * @var string
     */
    public static $tag = 'select';

    /**
     * @var [type]
     */
    public static $yield = [
        'options' => __NAMESPACE__ . '\OptionField'
    ];
}
