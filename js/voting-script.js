jQuery(document).ready(function ($) {
    var ajaxurl = frontendajax.ajaxurl; // Use localized variable

    $('#vote-yes, #vote-no').on('click', function () {
        var post_id = $('#voting-section').data('post-id');
        var vote_type = $(this).attr('id').replace('vote-', '');

        // Disable buttons after click
        $('#voting-section button').attr('disabled', 'disabled');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'vote_action',
                post_id: post_id,
                vote_type: vote_type,
            },
            success: function (response) {
                var data = JSON.parse(response);

                if (data.error) {
                    alert(data.error);
                } else {
                    // Mark the clicked button with a blue border
                    $('#' + vote_type).addClass('voted');
					$('#voting-section button').attr('disabled', 'disabled');

                    // Update the voting results
                    $('#voting-results').html('<span>THANK YOU FOR YOUR FEEDBACK.</span> Yes: ' + data.yes_percentage + '%, No: ' + data.no_percentage + '%');
                }
            },
        });
    });
});
