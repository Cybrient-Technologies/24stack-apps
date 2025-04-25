<?php
// JavaScript for the template module
header("Content-type: application/javascript");
require_once __DIR__ . '/../config.php';
?>
$(document).ready(function() {
    // Form submissions
    $('#form-add-item, #form-edit-item').on('submit', function(e) {
        e.preventDefault();
        $.post('<?php echo $module_post_url; ?>', $(this).serialize(), function() {
            window.location = '<?php echo "https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug"; ?>';
        }).fail(function() {
            alert('Error submitting form. Please try again.');
        });
    });

    // List item click
    $('.list-group-item').on('click', function() {
        var itemId = $(this).data('item-id');
        window.location = '<?php echo "https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug"; ?>&item_id=' + itemId;
    });
});

/**
 * Developer Notes:
 * - Handles form submissions and list interactions.
 * - TinyMCE is initialized in module.php if enabled.
 * - Customize for additional UI behavior.
 */
?>