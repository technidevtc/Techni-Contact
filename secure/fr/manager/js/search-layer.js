$(document).ready(function() {

  var search_layer_blocked_pages = new Array('lead-create');

  var allow_search_layer = true;

  for (i=0;i<search_layer_blocked_pages.length;i++)
    {
      if(window.location.pathname.match(search_layer_blocked_pages[i]))
        allow_search_layer = false;
    }

  var search_line = $('#search-bar');
  var search_form = $('#search-bar form');
/*
if(allow_search_layer)
	
	$(window).scroll(function () {  
          if ($(this).scrollTop() > search_line.offset().top) {
              var search_bottom_line = $('div[class=wrap_bottom_search]');
              if(search_bottom_line.html() == null){
                  search_form.wrap('<div class="wrap_bottom_search">');
                  var search_bottom_line = $('div[class=wrap_bottom_search]');
                  search_bottom_line.before('<div class="bottom_search_bg">');
                  search_form.css({'top' : '8px', 'right': '300px', 'position' : 'absolute'});
                  $('div[class=search-box]').css({'margin' : '3px 0 0'});
                  $('#top-buttons a:has(span[class*=plus])').each(function(){
                    search_form.after(this);
                  });
                  $('a:has(span[class*=plus])').css({'float': 'right', 'position': 'relative', 'display' : 'block', 'font': '12px Arial,Helvetica,sans-serif', 'font-weight' : 'bold', 'right': '10px', 'top':'3px'});
                  $('div[class=wrap_bottom_search]').mouseover(function(){
                    $('div[class=bottom_search_bg]').css({'opacity':'1','filter':'alpha(opacity=100)'});
                  }).mouseout(function(){
                    $('div[class=bottom_search_bg]').css({'opacity':'0.5','filter':'alpha(opacity=50)'});
                  });
                  $('table[class=auto-completion-box][id!=tabbed-search-input-AC-box]').css({'position' : 'fixed'});
                  $('table[class=auto-completion-box][id!=tabbed-search-input-AC-box]').offset({ left : search_form.offset().left});
                  $('table[class=auto-completion-box][id!=tabbed-search-input-AC-box]').css({ 'bottom' : search_form.height()+4, 'top' : ''});
              }

			  
				//Show Scroll Navigation Button
				//for the page /fr/manager/advertisers	=> Production -> GEstion des fournisseurs
				//if(window.location.pathname=='/fr/manager/advertisers/edit.php'){
				//	document.getElementById('advertiser_navigation_absolute_bottom_button_container').style.display = 'block';
				//}
				
			   
          } else {
            var search_bottom_line = $('div[class=wrap_bottom_search]');
              if(search_bottom_line.html() != null){
                  search_form.unwrap();
                  $('div[class=bottom_search_bg]').remove();
                  search_form.css({ 'position' : '', 'right' : '', 'top' : ''});
                  $('div[class=search-box]').css({'margin' : ''});
                  var search_box = $('input[name=search]');
                  $('a:has(span[class*=plus])').each(function(){
                    $('#top-buttons a:has(span[class*=ui-icon-folder-collapsed])').before(this);
                  });
                  $('a:has(span[class*=plus])').css({'float': 'left', 'top': '', 'right': ''});

                  $('table[class=auto-completion-box][id!=tabbed-search-input-AC-box]').css({'position' : 'absolute', 'bottom' : ''});
                  $('table[class=auto-completion-box][id!=tabbed-search-input-AC-box]').offset({top : search_box.offset().top + 23, left :  search_box.offset().left });
              }


				//Hide Scroll Navigation Button
				//for the page /fr/manager/advertisers	=> Production -> GEstion des fournisseurs
				//if(window.location.pathname=='/fr/manager/advertisers/edit.php'){
				//	document.getElementById('advertiser_navigation_absolute_bottom_button_container').style.display = 'none';
				//}
				
          }
  });*/

});