/*
 * @Author:    Dan Lewis (dan.lewis@deploy.co.uk)
 * @Copyright: 2024 Deploy Ecommerce (https://www.deploy.co.uk/)
 * @Package:   DeployEcommerce_DynamicSwatchLink
 */

define(['jquery'], function ($) {
    'use strict';
    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            _LoadProductMedia: function () {

                var $widget = this,
                    $this = $widget.element,
                    productData = this._determineProductData(),
                    mediaCallData,
                    mediaCacheKey,

                    /**
                     * Processes product media data
                     *
                     * @param {Object} data
                     * @returns void
                     */
                    mediaSuccessCallback = function (data) {
                        if (!(mediaCacheKey in $widget.options.mediaCache)) {
                            $widget.options.mediaCache[mediaCacheKey] = data;
                        }
                        $widget._ProductMediaCallback($this, data, productData.isInProductView);
                        setTimeout(function () {
                            $widget._DisableProductMediaLoader($this);
                        }, 300);
                    };
                var buildUrl = function (base, key, value) {
                    var sep = (base.indexOf('?') > -1) ? '&' : '?';
                    return base + sep + key + '=' + value;
                }
                let previousUrl = $this.parents('.product-item-info').find('a.product-item-link').attr("href");
                $this.parents('.product-item-info')
                    .find('a.product-item-link')
                    .attr("href", buildUrl(previousUrl, 'selected_id', this.getProduct()));
                $this.parents('.product-item-info')
                    .find('a.product-item-photo')
                    .attr("href", buildUrl(previousUrl, 'selected_id', this.getProduct()));
                if (!$widget.options.mediaCallback) {
                    return;
                }

                mediaCallData = {
                    'product_id': this.getProduct()
                };

                mediaCacheKey = JSON.stringify(mediaCallData);

                if (mediaCacheKey in $widget.options.mediaCache) {
                    $widget._XhrKiller();
                    $widget._EnableProductMediaLoader($this);
                    mediaSuccessCallback($widget.options.mediaCache[mediaCacheKey]);
                } else {
                    mediaCallData.isAjax = true;
                    $widget._XhrKiller();
                    $widget._EnableProductMediaLoader($this);
                    $widget.xhr = $.ajax({
                        url: $widget.options.mediaCallback,
                        cache: true,
                        type: 'GET',
                        dataType: 'json',
                        data: mediaCallData,
                        success: mediaSuccessCallback
                    }).done(function () {
                        $widget._XhrKiller();
                    });
                }
            }

        });
        return $.mage.SwatchRenderer;
    };
});
