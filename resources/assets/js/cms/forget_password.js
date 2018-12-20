$(function() {
    var $pleaseWaitContainer = $('.please-wait-container');
    $pleaseWaitContainer.hide();

    $('#submit-btn').on('click', function() {
        if ($('input[type=email]').val() !== '') {
            $(this).hide();
            $pleaseWaitContainer.show();
        }
    });
});