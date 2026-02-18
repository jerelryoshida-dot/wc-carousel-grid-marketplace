(function($) {
    'use strict';

    var WC_CGM_Admin = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $(document).on('change', '#wc_cgm_enable_tier_pricing', this.toggleTierPricing);
            $(document).on('click', '.wc-cgm-tier-heading', this.toggleTierRow);
        },

        toggleTierPricing: function() {
            var checked = $(this).is(':checked');
            if (checked) {
                $('.wc-cgm-tiers-wrap').slideDown();
            } else {
                $('.wc-cgm-tiers-wrap').slideUp();
            }
        },

        toggleTierRow: function() {
            $(this).closest('.wc-cgm-tier-row').toggleClass('collapsed');
        }
    };

    $(document).ready(function() {
        WC_CGM_Admin.init();
    });

})(jQuery);
