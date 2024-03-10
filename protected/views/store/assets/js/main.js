(function($){
	"use strict";
	jQuery(document).on('ready', function () {
        // Header Sticky
		$(window).on('scroll',function() {
            if ($(this).scrollTop() > 120){  
                $('.navbar-light').addClass("is-sticky");
            }
            else{
                $('.navbar-light').removeClass("is-sticky");
            }
        });
        
        // Home Slides
        $(".home-slides").owlCarousel({
            items: 1,
            nav: true,
            dots: false,
            touchDrag: false,
            mouseDrag: false,
            autoplay: true,
            smartSpeed: 700,
            loop: true,
            navText: [
            "<i class='icofont-rounded-left'></i>",
            "<i class='icofont-rounded-right'></i>"
            ]
        });
        $(".home-slides").on("translate.owl.carousel", function(){
            $(".home-slides .banner-image").removeClass("animated fadeInDown").css("opacity", "0");
            $(".home-slides h1").removeClass("animated fadeInUp").css("opacity", "0");
            $(".home-slides p").removeClass("animated zoomIn").css("opacity", "0");
            $(".home-slides .btn").removeClass("animated fadeInDown").css("opacity", "0");
            $(".home-slides .video-btn").removeClass("animated fadeInDown").css("opacity", "0");
        });
        $(".home-slides").on("translated.owl.carousel", function(){
            $(".home-slides .banner-image").addClass("animated fadeInDown").css("opacity", "1");
            $(".home-slides h1").addClass("animated fadeInUp").css("opacity", "1");
            $(".home-slides p").addClass("animated zoomIn").css("opacity", "1");
            $(".home-slides .btn").addClass("animated fadeInDown").css("opacity", "1");
            $(".home-slides .video-btn").addClass("animated fadeInDown").css("opacity", "1");
        });
        
        // Pricing Tabs
        $(".tab-slider-body").hide();
        $(".tab-slider-body:first").show();
        $(".tab-slider-nav li").on('click', function() {
            $(".tab-slider-body").hide();
            var activeTab = $(this).attr("rel");
            $("#"+activeTab).fadeIn();
            if($(this).attr("rel") == "yearly"){
                $('.tab-slider-tabs').addClass('slide');
            }else{
                $('.tab-slider-tabs').removeClass('slide');
            }
            $(".tab-slider-nav li").removeClass("active");
            $(this).addClass("active");
        });
        
        // FAQ Accordion
        $(function() {
            $('.accordion').find('.accordion-title').on('click', function(){
                // Adds Active Class
                $(this).toggleClass('active');
                // Expand or Collapse This Panel
                $(this).next().slideToggle('fast');
                // Hide The Other Panels
                $('.accordion-content').not($(this).next()).slideUp('fast');
                // Removes Active Class From Other Titles
                $('.accordion-title').not($(this)).removeClass('active');		
            });
        });
        
        // Counter
        $(".count").counterUp({
            delay: 20,
            time: 1500
        });
        
        // Shorting
		$(function(){
            $('.shorting').mixItUp();
        });
        
        // Popup Gallery
		$('.popup-btn').magnificPopup({
            type: 'image',
            gallery:{
                enabled:true
            }
        });
        
        // Popup Video
        $('.video-btn').magnificPopup({
            disableOn: 320,
            type: 'iframe',
            mainClass: 'mfp-fade',
            removalDelay: 160,
            preloader: false,
            fixedContentPos: false
        });
        
        // Feedback Slides
        $('.feedback-slides').owlCarousel({
            loop: true,
            autoplay: true,
            nav: false,
            mouseDrag: true,
            autoplayHoverPause: true,
            responsiveClass: true,
            dots: true,
            navText: [
            "<i class='icofont-double-left'></i>",
            "<i class='icofont-double-right'></i>"
            ],
            responsive:{
                0:{
                    items:1,
                },
                768:{
                    items:2,
                },
                1200:{
                    items:3,
                }
            }
        });
        
        // Partner Slider
        $('.partner-slider').owlCarousel({
            loop: true,
            autoplay:true,
            nav: false,
            mouseDrag: true,
            autoplayHoverPause: true,
            responsiveClass: true,
            dots: true,
            navText: [
            "<i class='icofont-double-left'></i>",
            "<i class='icofont-double-right'></i>"
            ],
            responsive:{
                0:{
                    items:2,
                },
                768:{
                    items:4,
                },
                1200:{
                    items:6,
                }
            }
        });
        
        // Blog Slider
        $('.blog-slider').owlCarousel({
            loop: true,
            autoplay:true,
            nav: false,
            mouseDrag: true,
            autoplayHoverPause: true,
            responsiveClass: true,
            dots: true,
            navText: [
            "<i class='icofont-double-left'></i>",
            "<i class='icofont-double-right'></i>"
            ],
            responsive:{
                0:{
                    items:1,
                },
                768:{
                    items:2,
                },
                1200:{
                    items:3,
                }
            }
        });
        
        // Project Slides
        $('.project-slides').owlCarousel({
            loop: true,
            autoplay: true,
            nav: false,
            mouseDrag: true,
            autoplayHoverPause: true,
            responsiveClass: true,
            dots: true,
            navText: [
            "<i class='icofont-double-left'></i>",
            "<i class='icofont-double-right'></i>"
            ],
            responsive:{
                0:{
                    items:1,
                },
                768:{
                    items:2,
                },
                1200:{
                    items:3,
                }
            }
        });
		
		// Go to Top
        $(function(){
            //Scroll event
            $(window).on('scroll', function(){
                var scrolled = $(window).scrollTop();
                if (scrolled > 300) $('.go-top').fadeIn('slow');
                if (scrolled < 300) $('.go-top').fadeOut('slow');
            });  
            //Click event
            $('.go-top').on('click', function() {
                $("html, body").animate({ scrollTop: "0" },  500);
            });
        });
		
		// Subscribe form
		$(".newsletter-form").validator().on("submit", function (event) {
			if (event.isDefaultPrevented()) {
			// handle the invalid form...
				formErrorSub();
				submitMSGSub(false, "Please enter your email correctly.");
			} else {
				// everything looks good!
				event.preventDefault();
			}
		});

		function callbackFunction (resp) {
			if (resp.result === "success") {
				formSuccessSub();
			}
			else {
				formErrorSub();
			}
		}
		function formSuccessSub(){
			$(".newsletter-form")[0].reset();
			submitMSGSub(true, "Thank you for subscribing!");
			setTimeout(function() {
				$("#validator-newsletter").addClass('hide');
			}, 4000)
		}
		function formErrorSub(){
			$(".newsletter-form").addClass("animated shake");
			setTimeout(function() {
				$(".newsletter-form").removeClass("animated shake");
			}, 1000)
		}
		function submitMSGSub(valid, msg){
			if(valid){
				var msgClasses = "validation-success";
			} else {
				var msgClasses = "validation-danger";
			}
			$("#validator-newsletter").removeClass().addClass(msgClasses).text(msg);
		}
		// AJAX MailChimp
		$(".newsletter-form").ajaxChimp({
			url: "https://envytheme.us20.list-manage.com/subscribe/post?u=60e1ffe2e8a68ce1204cd39a5&amp;id=42d6d188d9", // Your url MailChimp
			callback: callbackFunction
		});
    });
    
    // Preloader
    jQuery(window).on('load', function() {
        $('.preloader-area').fadeOut();
    });
}(jQuery));