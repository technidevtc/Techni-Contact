 /*$(document).ready(function() {

  var search_line = $('div[class=sub-header]');//tag-line
  var search_form = $('form[class=search]');
  var wrapper = $('#wrapper');


  var aujd = new Date();

  $(window).scroll(function () {
    var browserVersion = $.browser.version.split('.');
         if ($(this).scrollTop() > search_line.offset().top) {
              var search_bottom_line = $('div[class=wrap_bottom_search]');
              if(search_bottom_line.html() == ''){
                wrapper.css({'padding-bottom': '30px'});
                if(!cm.read('bottomSearchBar') || cm.read('bottomSearchBar') < aujd.getTime()){ //
                  var search_bottom_line = $('div[class=wrap_bottom_search]');
                  search_bottom_line.html(search_form);
                  search_bottom_line.css({left : (wrapper.offset().left + 6)});
                  search_bottom_line.show();
                  $('.wrap_bottom_search .search div').css({'float': 'none', 'position': 'absolute', 'left': '-12px'});
                  $('div[class=motto-center] form[class=search]').remove();
                  search_form.before('<span class=\"bottom_search_link_l\">Rechercher </span>');
                  search_form.after(lien_bottom_search);
                  search_form.css({'position' : 'relative', 'left' : '15%', 'bottom': '2px'});
                  if($.browser.msie && browserVersion[0] == 7){
                    search_form.css({'position' : 'relative', 'left' : '2%', 'bottom': '2px'});
                    $('.wrap_bottom_search .search div').css({'float': 'none', 'position': 'absolute', 'left': '120px'});
                  }
                  if($.browser.msie && browserVersion[0] == 9){
                    search_form.css({'position' : 'relative', 'left' : '14%', 'bottom': '2px'});
                    $('.wrap_bottom_search .search div').css({'float': 'none', 'position': 'absolute', 'left': '0px'});
                  }
                  search_form.find('div').css('width', '440px');
                  search_form.find('div input[class=text]').css('width', '428px');
                  $('input[name=search]').before('<input type="hidden" name="moteur" value="calque_recherche" />');
                  $('table[class=auto-completion-box]').css({'position' : 'fixed', 'width' : $('form[class=search] div').width()+4});
                  $('table[class=auto-completion-box]').offset({left : 0-(($('#header-in').offset().left)+($('form[class=search] div').offset().left)+1)});
                  $('table[class=auto-completion-box]').css({'bottom' : $('.wrap_bottom_search .search div').height()+4, 'top' : ''});
                  search_bottom_line.css({'left': 0 - search_bottom_line.width()});
                  search_bottom_line.delay(1000).animate({left: $('#header-in').offset().left}, {duration : 1000, step: function( now, fx ){
                        $('table[class=auto-completion-box]').css( "left", now+146 );
                      }
                  });
                  var date = new Date();
                  date.setHours(0,0,0,0);
                  date.setDate(date.getDate()+1);
                  var stringDate = date.getTime().toString();
                  cm.write('bottomSearchBar', stringDate, null, '/', JS_DOMAIN);

                }else{  
                  var search_bottom_line = $('div[class=wrap_bottom_search]');
                  search_bottom_line.html(search_form);
                  search_bottom_line.css({left : (wrapper.offset().left + 6)});
                  search_bottom_line.show();
                  $('.wrap_bottom_search .search div').css({'float': 'none', 'position': 'absolute', 'left': '-12px'});
                  $('div[class=motto-center] form[class=search]').remove();
                  search_form.before('<span class=\"bottom_search_link_l\">Rechercher </span>');
                  search_form.after(lien_bottom_search);
                  search_form.css({'position' : 'relative', 'left' : '15%', 'bottom': '2px'});
                  if($.browser.msie && browserVersion[0] == 7){
                    search_form.css({'position' : 'relative', 'left' : '2%', 'bottom': '2px'});
                    $('.wrap_bottom_search .search div').css({'float': 'none', 'position': 'absolute', 'left': '120px'});
                  }
                  if($.browser.msie && browserVersion[0] == 9){
                    search_form.css({'position' : 'relative', 'left' : '14%', 'bottom': '2px'});
                    $('.wrap_bottom_search .search div').css({'float': 'none', 'position': 'absolute', 'left': '0px'});
                  }
                  search_form.find('div').css('width', '440px');
                  search_form.find('div input[class=text]').css('width', '428px');
                  $('input[name=search]').before('<input type="hidden" name="moteur" value="calque_recherche" />');
                  $('table[class=auto-completion-box]').css({'position' : 'fixed', 'width' : $('form[class=search] div').width()+4});
                  $('table[class=auto-completion-box]').offset({left : ($('form[class=search] div').offset().left)+1});
                  $('table[class=auto-completion-box]').css({'bottom' : $('.wrap_bottom_search .search div').height()+4, 'top' : ''});
               // }
                var date = new Date();
                date.setHours(0,0,0,0);
                date.setDate(date.getDate()+1);
                var stringDate = date.getTime().toString();
                if(!cm.read('bottomSearchBar'))
                  cm.write('bottomSearchBar', stringDate, null, '/', JS_DOMAIN);

              }
          } else {
            wrapper.css({'padding-bottom': '0px'});
            var search_bottom_line = $('div[class=wrap_bottom_search]');
              if(search_bottom_line.html() != ''){

                  $('span[class=bottom_search_link_l]').remove();
                  $('span[class=bottom_search_link_r]').remove();
                  $('div[class=motto-center]').html(search_form);
                  $('div[class=wrap_bottom_search] form[class=search]').remove();
                  $('div[class=wrap_bottom_search]').hide();

                  search_form = $('form[class=search]')
                  search_form.css({'position' : 'relative', 'z-index' : '0'})
                  $('div[class=sub-header] .search div').css({'float': 'right', 'position': 'relative', 'left': '0px', 'width': '415px'});
                  $('div[class=sub-header] .search div input.text').css({'width': '380px'});
                  search_form.css({'left' : '0', 'bottom': '0'});
                  var search_box = $('input[name=search]');
                  $('input[name=moteur][type=hidden]').remove();

                  $('table[class=auto-completion-box]').css({'position' : 'absolute', 'bottom' : ''});
                  $('table[class=auto-completion-box]').offset({top : search_box.offset().top + 23, left :  $('form[class=search] div').offset().left});

              }
          }
  });

});

*/