jQuery(document).ready(function($) {
    $('.time-widget').each(function(i, el) {
        var weekdays = $(el).find('.weekdays a'),
            hours = $(el).find('.hours a'),
            business = $(el).find('a.business'),
            nonBusiness = $(el).find('a.non-business'),
            checkboxes = $(el).find('input:checkbox'),
            all = $(el).find('a.all');
        all.bind('click', function(e) {
            if ($(checkboxes).size() > $(checkboxes).filter(':checked').size()) {
                $(checkboxes).prop('checked', true);
            } else {
                $(checkboxes).prop('checked', false);
            }
        });
        weekdays.add(hours).add(business).add(nonBusiness).bind('click', function(e) {
            var className = '.' + $(this).attr('data-toggle'),
                current = $(checkboxes).filter(className);
            if ($(current).size() > $(current).filter(':checked').size()) {
                $(current).prop('checked', true);
            } else {
                $(current).prop('checked', false);
            }
        });
    });
});
