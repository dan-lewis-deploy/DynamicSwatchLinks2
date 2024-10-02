/*
 * @Author:    Dan Lewis (dan.lewis@deploy.co.uk)
 * @Copyright: 2024 Deploy Ecommerce (https://www.deploy.co.uk/)
 * @Package:   DeployEcommerce_DynamicSwatchLink
 */

define([
    "jquery",
    "Magento_Swatches/js/swatch-renderer"
], function ($) {

    return function (dynamicConfig) {

        let factory;
        let data;
        let options;
        let shownHeaders = [];
        let $headers;
        let userLang = navigator.language || navigator.userLanguage;
        let arrWidthsForTHs;
        let mainWidth;

        $.setListeners = function () {
            factory.$headers.on("click", function () {
                if ($(this).data("prop") != undefined) {
                    let sortProp = $(this).data("prop");
                    let sortWay = $(this).attr("data-way");
                    if (sortWay == "up") {
                        $(this).attr("data-way", "down");
                    } else {
                        $(this).attr("data-way", "up");
                    }
                    if (sortWay == "up") {
                        data.sort(function (a, b) {
                            if (a[sortProp] < b[sortProp]) return -1;
                            if (a[sortProp] > b[sortProp]) return 1;
                            if (a[sortProp] == b[sortProp]) return 0;
                        });
                    } else {
                        data.sort(function (a, b) {
                            if (a[sortProp] < b[sortProp]) return 1;
                            if (a[sortProp] > b[sortProp]) return -1;
                            if (a[sortProp] == b[sortProp]) return 0;
                        });
                    }
                    $.draw();
                }
            });
        };

        $.draw = function () {
            $(factory).hide();
            let html = "";
            data.forEach(function (x) {
                html += "<tr>";
                shownHeaders.forEach(function (y) {
                    let value = "";
                    if (x[y] != undefined) {
                        let type = jQuery.type(x[y]);
                        if (type == "boolean") {
                            value = x[y] ? "X" : "";
                        } else if (type == "string") {
                            value = x[y];
                        } else if (type == "number") {
                            value = x[y];
                        } else if (type == "date") {
                            value = renderDate(x[y]);
                        }
                    }
                    html += "<td>" + value + "</td>";
                });
                html += "</tr>";
                $(factory).find("tbody").html(html);
                if (options.tbodyHeight) {
                    $(factory).find("tbody").css({
                        "display": "block",
                        "overflow-y": "scroll",
                        "height": options.tbodyHeight,
                        "max-height": options.tbodyHeight,
                        "font-size": "12px"
                    });
                }
                let count = 0;
                let arrWidthsForTDs = [];
                let $modelTR = $(factory).find("tbody tr:first");
                let $headerTR = $(factory).find("thead tr:first");
                if ($modelTR.length == 1) {
                    //Setting the min-width of data columns
                    $modelTR.find("td").each(function (i) {
                        let w = $(this).outerWidth();
                        arrWidthsForTDs.push(w);
                        $(this).css("min-width", arrWidthsForTHs[i]);
                        count += w;
                    });
                    let ratios = [];
                    arrWidthsForTDs.forEach(function (x, i) {
                        ratios.push(x / count);
                    });
                    if ($headerTR.length == 1) {
                        $(factory).css("width", (mainWidth + 20) + ".px"); // mainWidth // (count+20)+".px"
                        $headerTR.find("th").each(function (i) {
                            $(this).css("width", parseInt((ratios[i] * mainWidth)) + ".px");
                            $(this).css("min-width", arrWidthsForTHs[i]);
                        });
                    }
                    $modelTR.find("td").each(function (i) {
                        $(this).css("width", parseInt((ratios[i] * mainWidth)) + ".px");
                    });
                }
                $(factory).show();
            });
            $.tableOptions();
        };

        $.tableOptions = function () {
            $('div[data-option-id]').each(function (index) {
                if (!$(this).attr('data-option-tooltip-value')) return;
                $(this).css('background-color', $(this).attr('data-option-tooltip-value'));
            });
            $('#tableScroller').find(
                '[data-option-type="1"], ' +
                '[data-option-type="2"], ' +
                '[data-option-type="0"], ' +
                '[data-option-type="3"]'
            ).SwatchRendererTooltip();
            $('.increaseqty').click(function (e) {
                e.preventDefault();
                var qty = $(this).closest("div.qty-box").find("input.qty-input");
                var newqty = parseInt(qty.val()) + parseInt(1);
                qty.val(newqty);
                return false;
            });
            $('.decreaseqty').click(function (e) {
                var qty = $(this).closest("div.qty-box").find("input.qty-input");
                var newqty = parseInt(qty.val()) - parseInt(1);
                if (newqty < 0) {
                    return false;
                }
                qty.val(newqty);
                return false;
            });
            $('#tableScroller td .swatch-option').click(function (e) {
                e.preventDefault();
                var optionId = $(this).attr('data-option-id');
                var swatchOption = $('#tableScroller .swatch-attribute-options')
                    .find('.swatch-option[data-option-id="' + optionId + '"]');
                swatchOption.click();
                return false;
            });
            $("#containerWidth").on("change", function () {
                let percentage = $(this).val() + "%";
                $("#container").css("width", percentage);
                $("#enlarger span").text(percentage);
                myTable.tableScroller("update", percentage);
            });
        };

        $.fn.tableScroller = function (action, input) {
            factory = this;
            if (action == "init") {
                data = input.data || [];
                options = input.options || {};
                factory.$headers = $(factory).find("thead th");
                if (factory.$headers.length > 0) {
                    factory.$headers.each(function () {
                        let prop = $(this).attr("data-prop");
                        if (typeof prop !== typeof undefined && prop !== false) {
                            shownHeaders.push(prop);
                        }
                    });
                }
                mainWidth = $(factory).parent().outerWidth() - 20;
                $.setListeners();
                arrWidthsForTHs = [];
                $(factory).find("thead").css({"display": "block", "overflow-y": "hidden"});
                let $headerTR = $(factory).find("thead tr:first");
                if ($headerTR.length == 1) {
                    $headerTR.find("th").each(function () {
                        arrWidthsForTHs.push($(this).css("width"));
                    });
                }
                $.draw();
                $(window).resize(function () {
                    mainWidth = $(factory).parent().outerWidth() - 20;
                    $.draw();
                });
                return (factory);
            } else if (action == "update") {
                mainWidth = $(factory).parent().outerWidth() - 20;
                if (input == "100%") {
                    mainWidth -= 60;
                }
                $.draw();
            }
        };

        let myTable = $("#tableScroller").tableScroller(
            "init", {"data": dynamicConfig.data, "options": {"tbodyHeight": "250px"}}
        );
        $("#tableScroller").SwatchRenderer({
            classes: {
                attributeClass: 'swatch-attribute',
                attributeLabelClass: 'swatch-attribute-label',
                attributeSelectedOptionLabelClass: 'swatch-attribute-selected-option',
                attributeOptionsWrapper: 'swatch-attribute-options',
                attributeInput: 'swatch-input',
                optionClass: 'swatch-option',
                selectClass: 'swatch-select',
                moreButton: 'swatch-more',
                loader: 'swatch-option-loading'
            },
            jsonConfig: dynamicConfig.jsonConfig,
            jsonSwatchConfig: dynamicConfig.jsonSwatchConfig
        });
        $('#tableScroller').find('.swatch-attribute').hide();

    }

});
