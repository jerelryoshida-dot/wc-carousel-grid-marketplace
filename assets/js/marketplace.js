(function($) {
    'use strict';

    var WC_CGM_Marketplace = {
        debug: false,
        isLoading: true,

        log: function(...args) {
            if (this.debug) {
                console.log('[WC_CGM]', ...args);
            }
        },

        currentCategory: 0,
        currentTier: 1,
        currentOffset: 0,
        limit: 12,

        init: function() {
            this.bindEvents();
            this.initCarousel();
            this.initDefaultTier();
            this.syncInitialPrices();
        },

        syncInitialPrices: function() {
            var $activeBtn = $('.wc-cgm-tier-btn.active');
            var $marketplace = $('.wc-cgm-marketplace');
            
            if ($activeBtn.length) {
                var activeTier = parseInt($activeBtn.data('tier')) || 1;
                if (activeTier > 0) {
                    this.updateAllPricingPanels(activeTier);
                }
            }
            
            this.hideLoading();
        },

        showLoading: function() {
            var $marketplace = $('.wc-cgm-marketplace');
            $marketplace.addClass('wc-cgm-loading').removeClass('wc-cgm-loaded');
            $marketplace.find('.wc-cgm-loading-overlay').removeClass('hidden');
            this.isLoading = true;
        },

        hideLoading: function() {
            var $marketplace = $('.wc-cgm-marketplace');
            
            setTimeout(function() {
                $marketplace.removeClass('wc-cgm-loading').addClass('wc-cgm-loaded');
                $marketplace.find('.wc-cgm-loading-overlay').addClass('hidden');
                WC_CGM_Marketplace.isLoading = false;
            }, 100);
        },

        initDefaultTier: function() {
            var $activeBtn = $('.wc-cgm-tier-btn.active');
            if ($activeBtn.length === 0) {
                $('.wc-cgm-tier-btn.wc-cgm-tier-entry').addClass('active');
            }
        },

        bindEvents: function() {
            $(document).on('click', '.wc-cgm-category-item', this.filterByCategory);
            $(document).on('click', '.wc-cgm-tier-btn', this.filterByTier);
            $(document).on('click', '.wc-cgm-add-to-cart', this.addToCart);
            $(document).on('click', '.wc-cgm-headcount-btn', this.updateQuantity);
            $(document).on('change', '.wc-cgm-quantity-input', this.updateTotal);
            $(document).on('change', '.wc-cgm-tier-select', this.updateTierPrice);
            $(document).on('change', '.wc-cgm-switch-input', this.updatePriceType);
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

            if (tier === 0) {
                WC_CGM_Marketplace.loadProducts();
            } else {
                WC_CGM_Marketplace.updateAllPricingPanels(tier);
            }
        },

        updateAllPricingPanels: function(tierLevel) {
            var visibleCount = 0;
            
            $('.wc-cgm-card').each(function() {
                var $card = $(this);
                var $panel = $card.find('.wc-cgm-pricing-panel');
                var $badge = $card.find('.wc-cgm-tier-badge');
                var $cardDesc = $card.find('.wc-cgm-card-desc');
                
                var hourlyPrice = parseFloat($panel.data('tier-' + tierLevel + '-hourly')) || 0;
                var monthlyPrice = parseFloat($panel.data('tier-' + tierLevel + '-monthly')) || 0;
                var tierName = $panel.data('tier-' + tierLevel + '-name') || '';
                var tierDescription = $panel.data('tier-' + tierLevel + '-description') || '';
                
                if (hourlyPrice <= 0 && monthlyPrice <= 0) {
                    $card.hide();
                    return;
                }
                
                $card.show();
                visibleCount++;
                
                var priceType = $panel.find('.wc-cgm-switch-input').is(':checked') ? 'hourly' : 'monthly';
                var newPrice = priceType === 'monthly' ? monthlyPrice : hourlyPrice;
                
                $panel.find('.wc-cgm-price-main')
                    .data('price', newPrice)
                    .html(WC_CGM_Marketplace.formatPrice(newPrice));
                
                $panel.find('.wc-cgm-total-price').data('monthly-price', monthlyPrice);
                
                $panel.find('.wc-cgm-add-to-cart').data('tier-level', tierLevel);
                
                var badgeClass = ['entry', 'mid', 'expert'][tierLevel - 1] || 'default';
                $badge
                    .removeClass('entry mid expert default')
                    .addClass(badgeClass)
                    .text(tierName);
                
                $panel.find('.wc-cgm-tier-description').text(tierDescription);
                
                $panel.find('.wc-cgm-quantity-input').trigger('change');
            });
            
            WC_CGM_Marketplace.updateSectionHeader(visibleCount);
        },

        loadProducts: function() {
            var $grid = $('.wc-cgm-grid');
            var limit = parseInt($grid.closest('.wc-cgm-marketplace').data('limit')) || WC_CGM_Marketplace.limit;

            this.showLoading();

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
                        
                        if (WC_CGM_Marketplace.currentTier > 0) {
                            WC_CGM_Marketplace.updateAllPricingPanels(WC_CGM_Marketplace.currentTier);
                        }
                    }
                },
                complete: function() {
                    $grid.removeClass('loading');
                    WC_CGM_Marketplace.hideLoading();
                }
            });
        },

        loadMore: function(e) {
            e.preventDefault();
            var $grid = $('.wc-cgm-grid');
            var $btn = $(this);
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
                    $btn.addClass('loading').html('<span class="dashicons dashicons-update wc-cgm-spin"></span> Loading...');
                },
                success: function(response) {
                    if (response.success) {
                        $grid.append(response.data.html);
                        if (!response.data.has_more) {
                            $('.wc-cgm-load-more').hide();
                        }
                        if (WC_CGM_Marketplace.currentTier > 0) {
                            WC_CGM_Marketplace.updateAllPricingPanels(WC_CGM_Marketplace.currentTier);
                        }
                    }
                },
                complete: function() {
                    $btn.removeClass('loading').text('Load More');
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

            WC_CGM_Marketplace.showLoading();

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
                        if (WC_CGM_Marketplace.currentTier > 0) {
                            WC_CGM_Marketplace.updateAllPricingPanels(WC_CGM_Marketplace.currentTier);
                        }
                    }
                },
                complete: function() {
                    WC_CGM_Marketplace.hideLoading();
                }
            });
        },

        addToCart: function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $card = $btn.closest('.wc-cgm-card');
            var $panel = $btn.closest('.wc-cgm-pricing-panel');

            var productId = $btn.data('product-id');
            var tierLevel = parseInt($btn.data('tier-level')) || 0;
            var priceType = $panel.find('.wc-cgm-switch-input').is(':checked') ? 'hourly' : ($panel.data('default-price-type') || 'monthly');
            var quantity = parseInt($panel.find('.wc-cgm-quantity-input').val()) || 1;

            WC_CGM_Marketplace.log('Add to cart:', { productId, tierLevel, priceType, quantity });

            var hasTiers = $card.data('has-tiers') || $panel.data('has-tiers');
            if (hasTiers === 'true' || hasTiers === true) {
                if (tierLevel <= 0) {
                    alert(wc_cgm_ajax.i18n.select_tier || 'Please select an experience level.');
                    return;
                }

                var hourlyPrice = parseFloat($panel.data('tier-' + tierLevel + '-hourly')) || 0;
                var monthlyPrice = parseFloat($panel.data('tier-' + tierLevel + '-monthly')) || 0;
                var selectedPrice = priceType === 'monthly' ? monthlyPrice : hourlyPrice;

                if (selectedPrice <= 0) {
                    var errorMsg = priceType === 'monthly'
                        ? 'Monthly pricing is not available for this experience level.'
                        : 'Hourly pricing is not available for this experience level.';
                    alert(wc_cgm_ajax.i18n.invalid_price_type || errorMsg);
                    return;
                }
            }

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
                    WC_CGM_Marketplace.log('Add to cart response:', response);
                    if (response.success) {
                        $btn.find('.wc-cgm-btn-text').text(wc_cgm_ajax.i18n.added_to_cart);
                        
                        $(document.body).trigger('wc_fragment_refresh');
                        $(document.body).trigger('added_to_cart', [
                            response.data.cart_item_key,
                            response.data.cart_count,
                            response.data.cart_total
                        ]);

                        setTimeout(function() {
                            $btn.find('.wc-cgm-btn-text').text('Add to Cart');
                        }, 2000);
                    } else {
                        alert(response.data.message || wc_cgm_ajax.i18n.error);
                        $btn.find('.wc-cgm-btn-text').text('Add to Cart');
                    }
                },
                error: function(xhr, status, error) {
                    WC_CGM_Marketplace.log('AJAX error:', xhr, status, error);
                    alert('Error: ' + error);
                    $btn.find('.wc-cgm-btn-text').text('Add to Cart');
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
            var monthlyPrice = parseFloat($panel.find('.wc-cgm-total-price').data('monthly-price')) || 0;
            var total = monthlyPrice * quantity;

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
            var priceType = $panel.find('.wc-cgm-switch-input').is(':checked') ? 'hourly' : 'monthly';

            var price = priceType === 'monthly' ? monthlyPrice : hourlyPrice;

            $panel.find('.wc-cgm-price-main').data('price', price);
            $panel.find('.wc-cgm-price-main').html(WC_CGM_Marketplace.formatPrice(price));

            $panel.find('.wc-cgm-add-to-cart').data('tier-level', $select.val());

            $panel.find('.wc-cgm-quantity-input').trigger('change');
        },

        updatePriceType: function(e) {
            var $input = $(this);
            var $panel = $input.closest('.wc-cgm-pricing-panel');
            var priceType = $input.is(':checked') ? 'hourly' : 'monthly';

            $panel.find('.wc-cgm-switch-label').removeClass('active');
            $panel.find('.wc-cgm-switch-label').eq(priceType === 'hourly' ? 2 : 0).addClass('active');

            $panel.find('.wc-cgm-add-to-cart').data('price-type', priceType);

            var currentTier = parseInt($panel.find('.wc-cgm-add-to-cart').data('tier-level')) || 1;
            var hourlyPrice = parseFloat($panel.data('tier-' + currentTier + '-hourly')) || 0;
            var monthlyPrice = parseFloat($panel.data('tier-' + currentTier + '-monthly')) || 0;
            var newPrice = priceType === 'monthly' ? monthlyPrice : hourlyPrice;
            
            $panel.find('.wc-cgm-price-main')
                .data('price', newPrice)
                .html(WC_CGM_Marketplace.formatPrice(newPrice));
            
            $panel.find('.wc-cgm-total-price').data('monthly-price', monthlyPrice);
            
            if (priceType === 'monthly') {
                $panel.find('.wc-cgm-price-sub').html(WC_CGM_Marketplace.formatPrice(hourlyPrice) + '/hr');
            } else {
                $panel.find('.wc-cgm-price-sub').html(WC_CGM_Marketplace.formatPrice(monthlyPrice) + '/mo');
            }
            
            $panel.find('.wc-cgm-quantity-input').trigger('change');
        },

        updateSectionHeader: function(count) {
            var text = count === 1 
                ? '1 role available' 
                : count + ' roles available';
            $('.wc-cgm-section-count').text(text);
        },

        formatPrice: function(price) {
            return '$' + price.toLocaleString('en-US', {
                minimumFractionDigits: 2,
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
