
(function( $ ) {

    var methods = {

        init: function (params) {

            var $widget = this,
                $hintToggle = $widget.find('.hint-toggle'),
                $hintContent = $widget.find('.hint-content');

            var options = $.extend({
                url: false
            }, params);

            $hintToggle.on('click', function() {
                if (options.url !== false) {
                    var hidden = $hintContent.is(':visible') ? 1 : 0;
                    $.ajax({
                        data: { value: hidden },
                        url: options.url
                    });
                }
                if (hidden) {
                    $hintContent.fadeOut(400, function () {
                        $widget.removeClass('hint-visible');
                    });
                } else {
                    $widget.addClass('hint-visible');
                    $hintContent.fadeIn();
                }
            });

        }

    };

    $.fn.fHint = function(method) {

        if ( methods[method] ) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('No such ' +  method + ' in jQuery.fHint');
        }

    };

})(jQuery);
