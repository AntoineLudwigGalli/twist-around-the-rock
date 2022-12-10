// Owl Carrousel settings
$(document).ready(function(){
    $('.owl-carousel').owlCarousel({
        items: 1,
        loop: true,
        center: true,
        nav: true,
        autoplay: true,
        lazyLoad: true,
        autoplayHoverPause: true,
        autoplayTimeout: 3000,
    });
});