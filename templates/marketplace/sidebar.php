<?php
defined('ABSPATH') || exit;

$categories = $categories ?? [];
$atts = $atts ?? [];
?>

<ul class="wc-cgm-category-nav">
    <?php foreach ($categories as $category) : 
        $is_active = $category['id'] === 0;
        $icon = $category['icon'] ?: 'grid';
    ?>
    <li class="wc-cgm-category-item <?php echo $is_active ? 'active' : ''; ?>" 
        data-category="<?php echo esc_attr($category['id']); ?>">
        
        <?php if ($icon) : ?>
        <span class="wc-cgm-category-icon">
            <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>"></span>
        </span>
        <?php endif; ?>
        
        <span class="wc-cgm-category-name"><?php echo esc_html($category['name']); ?></span>
        
        <span class="wc-cgm-category-count"><?php echo esc_html($category['count']); ?></span>
    </li>
    <?php endforeach; ?>
</ul>
