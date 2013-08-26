(function() {
  jQuery(document).ready(function($) {
    return $("#branches").change(function() {
      return $(location).attr("href", "post.php?post=" + ($(this).val()) + "&action=edit");
    });
  });

}).call(this);
