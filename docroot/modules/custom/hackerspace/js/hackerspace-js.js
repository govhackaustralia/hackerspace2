function toggleChecked() {
    jQuery('.field--name-field-captain input').not(this).prop('checked', false);
}
jQuery('#edit-field-team-members-wrapper').bind('DOMSubtreeModified', function(e) {
  if (e.target.innerHTML.length > 0) {
    jQuery('.field--name-field-captain input').unbind('change', toggleChecked).bind('change', toggleChecked);
  }
});
jQuery('.field--name-field-captain input').unbind('change', toggleChecked).bind('change', toggleChecked);
