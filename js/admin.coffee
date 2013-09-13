jQuery(document).ready ($) ->
  $("#branches").change ->
    $(location).attr "href", "post.php?post=#{$(this).val()}&action=edit"
