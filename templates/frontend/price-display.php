<?php
defined('ABSPATH') || exit;

$tiers = $tiers ?? [];
$price_type = $price_type ?? 'hourly';
?>

<div class="wc-cgm-price-display">
    <?php if (!empty($tiers)) : ?>
        <?php 
        $prices = array_filter(array_map(function($tier) use ($price_type) {
            return $price_type === 'monthly' ? $tier->monthly_price : $tier->hourly_price;
        }, $tiers));
        
        if (!empty($prices)) :
            $min_price = min($prices);
            $max_price = max($prices);
        ?>
        <div class="wc-cgm-price-range">
            <?php if ($min_price === $max_price) : ?>
                <?php echo wc_price($min_price); ?>/<?php echo esc_html($price_type === 'monthly' ? 'mo' : 'hr'); ?>
            <?php else : ?>
                <?php echo wc_price($min_price); ?> - <?php echo wc_price($max_price); ?>/<?php echo esc_html($price_type === 'monthly' ? 'mo' : 'hr'); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php else : ?>
        <div class="wc-cgm-price-regular">
            <?php echo $product->get_price_html(); ?>
        </div>
    <?php endif; ?>
</div>
