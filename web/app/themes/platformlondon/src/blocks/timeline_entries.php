<?php

use Carbon_Fields\Block;
use Carbon_Fields\Field;

Block::make(__('Timeline Entries'))
    ->add_fields(array(
        Field::make('separator', 'crb_separator', __('Timeline Entries'))
    ))
    ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
        $timeline_entries = get_posts([
            'post_type' => 'pl_timeline_entry',
            'post_status' => 'publish',
            'numberposts' => -1
        ]);
        if (!count($timeline_entries)) {
            echo '<p>No timeline entries found</p>';
            return;
        }
        $decades = [];
        usort($timeline_entries, function ($a, $b) use (&$decades) {
            $year_a = carbon_get_post_meta($a->ID, 'year');
            $year_b = carbon_get_post_meta($b->ID, 'year');

            // Side effect in sort function? Gross! But efficient...
            $decade_a = substr($year_a, 0, 3) . '0s';
            $decade_b = substr($year_b, 0, 3) . '0s';
            $decades[$decade_a] = true;
            $decades[$decade_b] = true;

            return $year_a < $year_b ? -1 : 1;
        });
        $decades = array_keys($decades);
        sort($decades);
        $current_decade = "1900s";
        ?>
        <div class="platform-timeline-links">
            <div>
                <ul class="platform-timeline-links__list">
                <?php foreach ($decades as $decade) : ?>
                    <li>
                        <a href="#<?= $decade ?>" class="btn-default bg-cream">
                            <?= $decade ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="platform-timeline">
            <div class="platform-timeline__line">
                <div class="platform-timeline__marker-container">
                    <div class="platform-timeline__marker">
                        <span class="platform-timeline__year">
                            <?= carbon_get_post_meta($timeline_entries[0]->ID, 'year') ?>
                        </span>
                        <div class="platform-timeline__circle"></div>
                    </div>
                    <div class="platform-timeline__active-line"></div>
                </div>
            </div>
            <div class="platform-timeline__entries">
                <?php foreach ($timeline_entries as $entry) {
                    $year = carbon_get_post_meta($entry->ID, 'year');
                    if (substr($year, 0, 3) !== substr($current_decade, 0, 3)) {
                        $current_decade = substr($year, 0, 3) . '0s';
                        $id = $current_decade;
                    } else {
                        $id = null;
                    } ?>
                    <div <?= $id ? 'id="' . $id . '"' : '' ?> 
                        class="platform-timeline__entry"
                        data-year="<?= $year ?>"
                    >
                        <div class="platform-timeline__circle"></div>
                        <h2><?= get_the_title($entry) ?></h2>
                        <div>
                            <?= apply_filters('the_content', $entry->post_content); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
    });