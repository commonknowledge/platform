<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make('post_meta', 'Member Metadata')
    ->where('post_type', '=', 'pl_member')
    ->add_fields(array(
        Field::make('text', 'position'),
    ));
