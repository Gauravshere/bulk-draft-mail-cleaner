jQuery(document).ready(function ($) {

    $('#select-all').on('change', function () {
        $('.draft-checkbox').prop('checked', $(this).is(':checked'));
    });

    $('#delete-selected').on('click', function () {
        let ids = [];

        $('.draft-checkbox:checked').each(function () {
            ids.push($(this).val());
        });

        if (ids.length === 0) {
            alert('Please select at least one draft.');
            return;
        }

        if (!confirm('Are you sure you want to delete selected drafts?')) {
            return;
        }

        $.post(bdmcData.ajaxUrl, {
            action: 'bdmc_delete_drafts',
            ids: ids,
            nonce: bdmcData.nonce
        }, function (response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data);
            }
        });
    });
});