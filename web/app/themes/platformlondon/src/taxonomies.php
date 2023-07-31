<?php

unregister_taxonomy_for_object_type('post_tag', 'post');

register_taxonomy('pl_resource_type', ['pl_resource'], [
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'query_var'         => true,
    'rewrite'           => ['slug' => 'resource-type'],
    'labels'            => [
        'name'              => _x('Resource types', 'taxonomy general name'),
        'singular_name'     => _x('Resource type', 'taxonomy singular name'),
    ]
]);

register_taxonomy('pl_post_type', ['post'], [
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'query_var'         => true,
    'rewrite'           => ['slug' => 'post-type'],
    'labels'            => [
        'name'              => _x('Post Types', 'taxonomy general name'),
        'singular_name'     => _x('Post type', 'taxonomy singular name'),
    ]
]);

register_taxonomy('pl_organisation', ['post', 'pl_multimedia', 'pl_project', 'pl_resource'], [
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'query_var'         => true,
    'rewrite'           => ['slug' => 'organisation'],
    'labels'            => [
        'name'              => _x('Organisations', 'taxonomy general name'),
        'singular_name'     => _x('Organisation', 'taxonomy singular name'),
    ]
]);

register_taxonomy('pl_player', ['post', 'pl_multimedia', 'pl_project'], [
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'query_var'         => true,
    'rewrite'           => ['slug' => 'player'],
    'labels'            => [
        'name'              => _x('Players', 'taxonomy general name'),
        'singular_name'     => _x('Player', 'taxonomy singular name'),
    ]
]);

register_taxonomy('pl_issue', ['post', 'pl_multimedia', 'pl_project', 'pl_resource'], [
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'query_var'         => true,
    'rewrite'           => ['slug' => 'issue'],
    'labels'            => [
        'name'              => _x('Issues', 'taxonomy general name'),
        'singular_name'     => _x('Issue', 'taxonomy singular name'),
    ]
]);

register_taxonomy('pl_place', ['post', 'pl_multimedia', 'pl_project', 'pl_resource'], [
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'query_var'         => true,
    'rewrite'           => ['slug' => 'place'],
    'labels'            => [
        'name'              => _x('Places', 'taxonomy general name'),
        'singular_name'     => _x('Place', 'taxonomy singular name'),
    ]
]);

register_taxonomy('pl_project_type', ['pl_project', 'pl_resource'], [
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'query_var'         => true,
    'rewrite'           => ['slug' => 'project-type'],
    'labels'            => [
        'name'              => _x('Project Types', 'taxonomy general name'),
        'singular_name'     => _x('Project type', 'taxonomy singular name'),
    ]
]);
