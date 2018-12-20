$(function() {
    var current = location.pathname;
    $('.side_menu a').each(function(){
        var $this = $(this);
        // if the current path is like this link, make it active
        if (current.indexOf($this.attr('href')) !== -1) {
            $this.addClass('active');
        }
    });

    if (current.indexOf('banner/region') !== -1) {
        $('.sub_menu_toggle').attr('aria-expanded', true);
        $('.banner_menu_items').addClass('show');
    }

    if (current.indexOf('banner/top') !== -1 || current.indexOf('banner/bottom') !== -1) {
        $('.sub_menu_toggle').attr('aria-expanded', true);
        $('.banner_menu_items').addClass('show');
        $('.banner_sub_menu_items').addClass('show');
    }
});