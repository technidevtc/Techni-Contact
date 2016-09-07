<div id="account-contact-dialog" title="Contacter notre service commercial"></div>
<script type="text/javascript">

$('#account-contact-dialog').dialog({
  width: 480,
  autoOpen: false,
  modal: true,
  draggable: false,
  resizable: false ,
  beforeClose: function(event, ui){
    var scrollTop = $(window).scrollTop();
    location.hash = location.hash.replace(/account-contact-dialog_\d+,\d+\|?/, "");
    $(window).scrollTop(scrollTop);
  }
})

// Postal code autocomplete
var champCodePostal = $('input[name^=cp]');

champCodePostal.live('keyup', function(){
  if($(this).val().match('[0-9]{5}') ){ //&& $("input[name='reversoReversed']").val() == 0
    var $champRef = $(this);
    var suffixe = $(this).attr('name').indexOf("_l") > 0 ? '_l' : '';
    $.ajax({
      type: "GET",
      data: "code_postal="+$(this).val(),
      dataType: "json",
      url: HN.TC.Locals.RessourcesURL+"/ajax/AJAX_codesPostaux.php",
      success: function(data) {

        var refBox = $champRef.closest('ul').find('input[name=ville'+suffixe+']');
        if(data['reponses'].length > 1){
          var html = '<table id="cpAutocomplete" class="auto-completion-box" style="position: absolute; z-index: 1004; min-width: 221px; top: '+(refBox.offset().top + refBox.height() + 7)+'px; left: '+refBox.offset().left+'px; -moz-user-select: none;" >';
          $.each(data['reponses'], function(){
            html += '<tr class=""><td class="prop">'+this.commune+'</td><td class="results"></td></tr>';
          });
          html += '</table>';

          $('#cpAutocomplete').remove(); // avoid multiple layers in case of multiple keyups
          $('body').append(html);

          $.each($('#cpAutocomplete tr'), function(){
            $(this).mouseenter(function(){
              $(this).addClass('over');
            }).mouseleave(function(){
              $(this).removeClass('over');
            }).click(function(){
              refBox.val($(this).find('td.prop').html());
              $('#cpAutocomplete').remove();
            });
          });

          refBox.blur(function(){
            setTimeout(function(){$('#cpAutocomplete').remove();}, 200);
          });

        }else if(data['reponses'].length == 1){
          refBox.val(data['reponses'][0].commune);
        }
      }
    });
  } // endif(id == champCodePostal)
});
// Postal code 

// close an eventual postal code autocompletion layer on closing the popup
$('#account-create-address-form-dialog').bind('dialogclose', function() {
     $('#cpAutocomplete').remove();
 });
 
 // shows attachment related to a post
$(".conversation-block .post-clip").live("click", function(){
  var $this = $(this);
  $this.next().css({ left: $this.position().left+20 }).toggle(300);
});

</script>
</div><!-- ends white-bg -->