$(document).ready(function() {
    // Manejar el clic en los elementos del menú
    $('.treeview > a').click(function(e) {
        e.preventDefault();
        var parent = $(this).parent();
        var submenu = parent.find('> .treeview-menu');
        
        // Cerrar otros submenús
        $('.treeview-menu').not(submenu).slideUp();
        $('.treeview').not(parent).removeClass('active');
        
        // Alternar el submenú actual
        submenu.slideToggle();
        parent.toggleClass('active');
    });

    // Manejar el clic en los elementos del submenú
    $('.treeview-menu > li > a').click(function() {
        $('.treeview-menu > li').removeClass('active');
        $(this).parent().addClass('active');
    });

    // Ajustar el menú cuando la ventana cambia de tamaño
    $(window).resize(function() {
        if ($(window).width() < 768) {
            $('.main-sidebar').css('transform', 'translate(-250px, 0)');
            $('.content-wrapper').css('margin-left', '0');
        } else {
            $('.main-sidebar').css('transform', 'translate(0, 0)');
            $('.content-wrapper').css('margin-left', '250px');
        }
    });

    // Manejar el botón de alternar menú
    $('.sidebar-toggle').click(function(e) {
        e.preventDefault();
        if ($(window).width() >= 768) {
            if ($('.main-sidebar').css('transform') === 'matrix(1, 0, 0, 1, 0, 0)') {
                $('.main-sidebar').css('transform', 'translate(-250px, 0)');
                $('.content-wrapper').css('margin-left', '0');
            } else {
                $('.main-sidebar').css('transform', 'translate(0, 0)');
                $('.content-wrapper').css('margin-left', '250px');
            }
        } else {
            $('.main-sidebar').css('transform', 'translate(0, 0)');
        }
    });
}); 