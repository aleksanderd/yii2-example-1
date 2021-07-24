
(function ($) {

    var methods = {

        init: function (params) {

            var options = $.extend({
                checkboxSelector: '',
                inputSelector: '',
                valueSelector: '',
                defaultValue: null
            }, params);

            var $checkbox = $(options.checkboxSelector),
                $input = $(options.inputSelector),
                $value = $(options.valueSelector),
                inputValue = '', tmp;

            var setInput = function(value, enabled) {
                enabled = enabled !== false;
                if (value !== null && value !== undefined && value.length > 0) {
                    //console.log(value);
                    if (tmp = $input.data('ionRangeSlider')) {
                        tmp.update({from: value, disable: !enabled});
                        return;
                    } else if (tmp = $input.attr('data-krajee-spectrum')) {
                        $('#' + $input.attr('id') + '-source').spectrum(enabled ? 'enable' : 'disable');
                    }
                    if ($input.prop('tagName') == 'SELECT' && $input.attr('multiple')) {
                        //console.log($input);
                        //console.log(typeof value);
                        //console.log(value);
                        $input.find('option').removeAttr('selected');
                        tmp = typeof value == 'string' ? value.split(',') : value;
                        for (var i = 0; i < tmp.length; i++) {
                            $input.find('option[value="' + tmp[i] + '"]').attr('selected', true);
                        }
                    }
                    $input.val(value).change();
                }
                if (enabled === false) {
                    $input.attr('disabled', 'disabled');
                } else {
                    $input.removeAttr('disabled');
                }
            };

            $checkbox.on('change', function() {
                //console.log('111');
                if ($checkbox.val() > 0) {
                    setInput(inputValue, true);
                } else {
                    inputValue = $input.val();
                    setInput(options.defaultValue, false);
                    $value.removeAttr('value');
                }
            }).change();

            $input.on('change', function() {
                var newValue = $checkbox.val() > 0 ? $input.val() : options.defaultValue;
                //console.log(newValue);
                if (newValue != $value.val()) {
                    $value.val(newValue).change();
                }
            });

            if (tmp = $input.attr('data-krajee-spectrum')) {
                $('#' + $input.attr('id') + '-source').on('change', function() {
                    $input.change();
                });
            }
            return this;
        }
    };

    $.fn.VariableWidget = function(method) {

        if ( methods[method] ) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('No such ' +  method + ' in jQuery.RuleLineWidget');
        }

    };

})(jQuery);
