<script>
    $(function() {
        var $passwordWaitMsg = $('.password-wait-msg');

        $('#save-password-btn').on('click', function() {
            var $passwordBtnContainer = $('.password-btn-container');
            $passwordBtnContainer.hide();
            $passwordWaitMsg.text('{{ __('cms_master.please_wait') }}...');
            $passwordWaitMsg.show();

            var $changePasswordModal = $('#changePasswordModal input');
            var value = {};
            $changePasswordModal.each(function() {
                value[this.name] = $(this).val();
            });

            value['_token'] = '{{ csrf_token() }}';

            $.ajax({
                method: "PUT",
                url: '/cms/user/password',
                data: value
            }).done(function() {
                $passwordWaitMsg.text('{{ __('cms_master.edit_success') }}');
                setTimeout(function() {
                    location.reload();
                }, 800);
            }).fail(function(res) {
                $passwordWaitMsg.text(res.responseJSON.error);
                $passwordBtnContainer.show();
            });
        });

        $profileWaitMsg.hide();
        $passwordWaitMsg.hide();
    });
</script>