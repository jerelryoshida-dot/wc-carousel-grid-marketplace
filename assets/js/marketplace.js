(function($) {
    'use strict';

    var WC_CGM_Marketplace = {
        currentCategory: 0,
        currentTier: 0,
        currentOffset: 0,
        limit: 12,

        init: function() {
            this.bindEvents();
            this.initCarousel();
        },

        bindEvents: function() {
            $(document).on('click', '.wc-cgm-category-item', this.filterByCategory);
            $(document).on('click', '.wc-cgm-tier-btn', this.filterByTier);
            $(document).on('click', '.wc-cgm-add-to-cart', this.addToCart);
            $(document).on('click', '.wc-cgm-headcount-btn', this.updateQuantity);
            $(document).on('change', '.wc-cgm-quantity-input', this.updateTotal);
            $(document).on('change', '.wc-cgm-tier-select', this.updateTierPrice);
            $(document).on('click', '.wc-cgm-price-type-btn', this.updatePriceType);
            $(document).on('click', '.wc-cgm-load-more', this.loadMore);
            $(document).on('input', '.wc-cgm-search-input', this.debounce(this.searchProducts, 300));
        },

        filterByCategory: function(e) {
            e.preventDefault();
            var $this = $(this);
            var categoryId = $this.data('category');

            WC_CGM_Marketplace.currentCategory = categoryId;
            WC_CGM_Marketplace.currentOffset = 0;

            $('.wc-cgm-category-item').removeClass('active');
            $this.addClass('active');

            WC_CGM_Marketplace.loadProducts();
        },

        filterByTier: function(e) {
            e.preventDefault();
            var $this = $(this);
            var tier = $this.data('tier');

            WC_CGM_Marketplace.currentTier = tier;
            WC_CGM_Marketplace.currentOffset = 0;

            $('.wc-cgm-tier-btn').removeClass('active');
            $this.addClass('active');

            WC_CGM_Marketplace.loadProducts();
        },

        loadProducts: function() {
            var $grid = $('.wc-cgm-grid');
            var limit = parseInt($grid.closest('.wc-cgm-marketplace').data('limit')) || WC_CGM_Marketplace.limit;

            $.ajax({
                url: wc_cgm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wc_cgm_filter_products',
                    nonce: wc_cgm_ajax.nonce,
                    category: WC_CGM_Marketplace.currentCategory,
                    tier: WC_CGM_Marketplace.currentTier,
                    limit: limit,
                    offset: WC_CGM_Marketplace.currentOffset
                },
                beforeSend: function() {
                    $grid.addClass('loading');
                },
                success: function(response) {
                    if (response.success) {
                        if (WC_CGM_Marketplace.currentOffset === 0) {
                            $grid.html(response.data.html);
                        } else {
                            $grid.append(response.data.html);
                        }
                        WC_CGM_Marketplace.updateSectionHeader(response.data.count);
                    }
                },
                complete: function() {
                    $grid.removeClass('loading');
                }
            });
        },

        loadMore: function(e) {
            e.preventDefault();
            var $grid = $('.wc-cgm-grid');
            var limit = parseInt($grid.closest('.wc-cgm-marketplace').data('limit')) || WC_CGM_Marketplace.limit;

            WC_CGM_Marketplace.currentOffset += limit;

            $.ajax({
                url: wc_cgm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wc_cgm_load_more',
                    nonce: wc_cgm_ajax.nonce,
                    category: WC_CGM_Marketplace.currentCategory,
                    tier: WC_CGM_Marketplace.currentTier,
                    limit: limit,
                    offset: WC_CGM_Marketplace.currentOffset
                },
                beforeSend: function() {
                    $(this).addClass('loading').text('Loading...');
                },
                success: function(response) {
                    if (response.success) {
                        $grid.append(response.data.html);
                        if (!response.data.has_more) {
                            $('.wc-cgm-load-more').hide();
                        }
                    }
                },
                complete: function() {
                    $(this).removeClass('loading').text('Load More');
                }
            });
        },

        searchProducts: function(e) {
            var search = $(e.target).val();
            var $grid = $('.wc-cgm-grid');

            if (search.length < 2) {
                WC_CGM_Marketplace.currentOffset = 0;
                WC_CGM_Marketplace.loadProducts();
                return;
            }

            $.ajax({
                url: wc_cgm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wc_cgm_search_products',
                    nonce: wc_cgm_ajax.nonce,
                    search: search,
                    limit: 12
                },
                success: function(response) {
                    if (response.success) {
                        $grid.html(response.data.html);
                        WC_CGM_Marketplace.updateSectionHeader(response.data.count);
                    }
                }
            });
        },

        addToCart: function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $card = $btn.closest('.wc-cgm-card');
            var $panel = $btn.closest('.wc-cgm-pricing-panel');

            var productId = $btn.data('product-id');
            var tierLevel = $btn.data('tier-level') || 1;
            var priceType = $btn.data('price-type') || 'hourly';
            var quantity = parseInt($panel.find('.wc-cgm-quantity-input').val()) || 1;

            $btn.addClass('loading');
            $btn.find('.wc-cgm-btn-text').text('Adding...');

            $.ajax({
                url: wc_cgm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wc_cgm_add_to_cart',
                    nonce: wc_cgm_ajax.nonce,
                    product_id: productId,
                    quantity: quantity,
                    tier_level: tierLevel,
                    price_type: priceType
                },
                success: function(response) {
                    if (response.success) {
                        $btn.find('.wc-cgm-btn-text').text(wc_cgm_ajax.i18n.added_to_cart);
                        $(document.body).trigger('wc_fragment_refresh');

                        setTimeout(function() {
                            $btn.find('.wc-cgm-btn-text').text('Add to Cart');
                        }, 2000);
                    } else {
                        alert(response.data.message || wc_cgm_ajax.i18n.error);
                        $btn.find('.wc-cgm-btn-text').text('Add to Cart');
                    }
                },
                complete: function() {
                    $btn.removeClass('loading');
                }
            });
        },

        updateQuantity: function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $input = $btn.siblings('.wc-cgm-quantity-input');
            var action = $btn.data('action');
            var currentVal = parseInt($input.val()) || 1;

            if (action === 'increase') {
                $input.val(Math.min(currentVal + 1, 99));
            } else if (action === 'decrease') {
                $input.val(Math.max(currentVal - 1, 1));
            }

            $input.trigger('change');
        },

        updateTotal: function(e) {
            var $input = $(this);
            var $panel = $input.closest('.wc-cgm-pricing-panel');
            var quantity = parseInt($input.val()) || 1;
            var price = parseFloat($panel.find('.wc-cgm-price-main').data('price')) || 0;
            var total = price * quantity;

            $panel.find('.wc-cgm-total-price').data('total', total);

            var formattedTotal = WC_CGM_Marketplace.formatPrice(total);
            $panel.find('.wc-cgm-total-price').html(formattedTotal + '/mo');
        },

        updateTierPrice: function(e) {
            var $select = $(this);
            var $panel = $select.closest('.wc-cgm-pricing-panel');
            var $option = $select.find('option:selected');

            var hourlyPrice = parseFloat($option.data('hourly')) || 0;
            var monthlyPrice = parseFloat($option.data('monthly')) || 0;
            var priceType = $panel.find('.wc-cgm-price-type-btn.active').data('price-type') || 'hourly';

            var price = priceType === 'monthly' ? monthlyPrice : hourlyPrice;

            $panel.find('.wc-cgm-price-main').data('price', price);
            $panel.find('.wc-cgm-price-main').html(WC_CGM_Marketplace.formatPrice(price));

            $panel.find('.wc-cgm-add-to-cart').data('tier-level', $select.val());

            $panel.find('.wc-cgm-quantity-input').trigger('change');
        },

        updatePriceType: function(e) {
            var $btn = $(this);
            var $panel = $btn.closest('.wc-cgm-pricing-panel');
            var priceType = $btn.data('price-type');

            $panel.find('.wc-cgm-price-type-btn').removeClass('active');
            $btn.addClass('active');

            $panel.find('.wc-cgm-add-to-cart').data('price-type', priceType);

            $panel.find('.wc-cgm-tier-select').trigger('change');
        },

        updateSectionHeader: function(count) {
            var text = count === 1 
                ? '1 role available' 
                : count + ' roles available';
            $('.wc-cgm-section-count').text(text);
        },

        formatPrice: function(price) {
            return '$' + price.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        },

        debounce: function(func, wait) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    func.apply(context, args);
                }, wait);
            };
        },

        initCarousel: function() {
            var $grid = $('.wc-cgm-grid.wc-cgm-hybrid');

            if ($grid.length && $(window).width() <= 768) {
                $grid.attr('data-carousel', 'true');
            }
        }
    };

    $(document).ready(function() {
        WC_CGM_Marketplace.init();
    });

    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/wc_cgm_marketplace.default', function($scope) {
            WC_CGM_Marketplace.init();
        });
    });

})(jQuery);
