jQuery.extend({
  
  sendFax: function(faxNumber, itemId, context)
    {
        var retour = $.ajax({
              url:'../ressources/ajax/AJAX_send-fax.php',
              secureuri:false,
              method: 'get',
              dataType: 'json',
              data:{faxNumber: faxNumber , itemId: itemId, context: context},
              async: false,
              success: function (data, status, toto)
              {
                      if(data.error)
                      {
                              alert(data.error);
                      }else
                      {
                              alert(data);
                      }
              },
              error: function (data, status, e)
              {
                      alert(e);
              }
      }).responseText;
      return $.parseJSON(retour);
    }

})

