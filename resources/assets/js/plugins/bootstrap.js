
jQuery(document).ready(function ($) {
    karlaJs();
    karlaBootJs();
    karlaSystemJs();

    $(document).on('ajax:modal:loaded', function (e, $this) {
        //karlaSystemJs();
    });

    $(document).on('ajax:loaded', function (e, $this) {
        karlaJs();
    });

    $(document).on('form:submit', function (e, $this) {
        var form = getForm($this);
        form.submit();
    });
});
