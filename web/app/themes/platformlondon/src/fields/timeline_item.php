<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make('post_meta', 'Timeline Date')
    ->where('post_type', '=', 'pl_timeline_entry')
    ->add_fields(array(
        Field::make('text', 'year', "Year (YYYY)")->set_attribute('type', 'number')
            ->set_required(true),
    ));
