'use strict'

angular
	.module('theme.directives', [])
	
	//Open Close Block
	.directive('panelControlCollapse', function () {
		return {	  
		  restrict: 'EAC',
		  link: function (scope, element, attr) {
			element.bind('click', function () {
			  $(element).toggleClass("fa-chevron-down fa-chevron-up");
			  $(element).closest(".panel").find('.panel-body').slideToggle({duration: 200});
			  $(element).closest(".panel-heading").toggleClass('rounded-bottom');
			})
			return false;
		  }
		};
	  })

	 
	//Scroll To top 
	.directive('backToTop', function () {
		return {
		  restrict: 'AE',
		  link: function (scope, element, attr) {
			element.click( function (e) {	
				
				//jQuery.noConflict();
				jQuery('html, body').animate({scrollTop: 0}, 'slow');	
	
			  //$(window).scrollTop(0); //This one work also for the IE
			  //$('body').scrollTop(0);
			});
		  }
		}
	  })