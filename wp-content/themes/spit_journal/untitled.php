<?php


$current_cat = get_query_var(‘cat’);
$temp = $wp_query;
$wp_query = null;
$wp_query = new WP_Query();
$wp_query->query(array(
‘post_type’=>’books’,
‘paged’ => $paged,
‘posts_per_page’ => 3,
‘cat’=> $current_cat
));

while ($wp_query->have_posts()) : $wp_query->the_post(); ?>






<?php endwhile; ?>// End the loop.
 <?php previous_posts_link('&laquo; Newer') ?>
    <?php next_posts_link('Older &raquo;') ?>

<?php $wp_query = null; ?>
<?php $wp_query = $temp; ?> // Reset







