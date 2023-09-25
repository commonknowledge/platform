<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make('post_meta', 'Related Content')
    ->where('post_type', 'IN', ['post', 'pl_project', 'pl_resource'])
    ->add_fields(array(
        Field::make('association', 'related', 'Related')
            ->set_types([
                [
                    'type'      => 'post',
                    'post_type' => 'pl_resource',
                ],
                [
                    'type'      => 'post',
                    'post_type' => 'post',
                ],
                [
                    'type'      => 'post',
                    'post_type' => 'pl_project',
                ],
                [
                    'type'      => 'post',
                    'post_type' => 'pl_multimedia',
                ]
            ])
    ));

// Restrict related posts search to title only
// https://github.com/htmlburger/carbon-fields/issues/957
function pl_search_by_title($options)
{
    $options['search_by_title'] = $options['s'] ?? "";
    unset($options['s']);
    return $options;
}

add_filter("carbon_fields_association_field_options_related_post_pl_resource", 'pl_search_by_title');

add_filter("carbon_fields_association_field_options_related_post_post", 'pl_search_by_title');

add_filter("carbon_fields_association_field_options_related_post_pl_project", 'pl_search_by_title');

add_filter("carbon_fields_association_field_options_related_post_pl_multimedia", 'pl_search_by_title');

add_filter('posts_where', function ($where, $wp_query) {
    global $wpdb;

    $search_by_title = $wp_query->get('search_by_title');
    if (!empty($search_by_title)) {
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql($wpdb->esc_like($search_by_title)) . '%\'';
    }

    return $where;
}, 10, 2);
