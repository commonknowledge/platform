<?php

register_post_type(
    'pl_project',
    array(
        'labels'      => array(
            'name'          => 'Projects',
            'singular_name' => 'Project',
        ),
        'public'      => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-megaphone',
        'rewrite' => array('slug' => 'project'),
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
        'taxonomies' => array('category')
    )
);

register_post_type(
    'pl_member',
    array(
        'labels'      => array(
            'name'          => 'Members',
            'singular_name' => 'Member',
        ),
        'public'      => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-admin-users',
        'rewrite' => array('slug' => 'member'),
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
        'taxonomies' => array()
    )
);

register_post_type(
    'pl_resource',
    array(
        'labels'      => array(
            'name'          => 'Resources',
            'singular_name' => 'Resource',
        ),
        'public'      => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-book',
        'rewrite' => array('slug' => 'resource'),
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
        'taxonomies' => array("category", "content-type")
    )
);

register_post_type(
    'pl_timeline_entry',
    array(
        'labels'      => array(
            'name'          => 'Timeline Entries',
            'singular_name' => 'Timeline Entry',
        ),
        'public'      => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-calendar',
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'author'),
        'exclude_from_search' => true,
        'taxonomies' => []
    )
);
