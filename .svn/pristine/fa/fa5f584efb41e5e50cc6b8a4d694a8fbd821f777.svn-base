var loadingImage = 'ajax-loader-big.gif';
var LoadingBar = '<div style="padding:20px;text-align:center;"><img src="../style/img/'+loadingImage+'" /></div>';
var LoadingBarmin = '<div style="padding:2px;text-align:center;"><img style="height:9px;" src="../style/img/'+loadingImage+'" /></div>';
var LoadingBarminm = '<div style="display: initial;padding:2px;text-align:center;"><img style="height:9px;" src="../style/img/'+loadingImage+'" /></div>';

$(document).ready(function() {
  $(".openPopup").click(function() {
    $("#loading").show();
    var popupId = $(this).data('popup-id');
    var $popupContainer = $("#" + popupId);
    var $this = $(this);
    var buttonPosition = $this.position();
    var buttonWidth = $this.outerWidth();
    var buttonHeight = $this.outerHeight();
    var screenWidth = $(window).width();
    var popupLeft;    
    if (screenWidth <= 600) {
		popupLeft = screenWidth / 2 - $popupContainer.outerWidth() / 2;
    } else {
		popupLeft = buttonPosition.left + buttonWidth / 2 - $popupContainer.outerWidth() / 2;
    }    
    $popupContainer.css({
		top: buttonPosition.top + buttonHeight + "px",
		left: popupLeft
    }).fadeIn(function() {
		$("#loading").hide(); // Ховаємо loading indicator після відображення попапу
    });
  });
  $(".popupContainer").on("click", function(event) {
    if ($(event.target).is(".popupContainer") || $(event.target).is(".closePopup")) {
		$(this).fadeOut();
    }
  });
  $(document).on("click", ".closePopup", function(event) {
		var $popupContainer = $(this).closest(".popupContainer");
		$popupContainer.fadeOut();
  });
});
document.addEventListener("DOMContentLoaded", function() {
    var menuLinkOlt = document.querySelector('.menu_main_olt');
    var menu_olt = document.querySelector('.dr_menu_olt');

    if (menuLinkOlt && menu_olt) {  // Перевірка на наявність елементів
        var menuPositionLeft = menuLinkOlt.getBoundingClientRect().left - 20;
        var menuPositionTop = menuLinkOlt.getBoundingClientRect().top - 40;

        menuLinkOlt.addEventListener("mouseenter", function() {
            menu_olt.style.display = "block";
            menu_olt.style.left = menuPositionLeft + 'px';
            menu_olt.style.top = menuPositionTop + 'px';
        });

        menu_olt.addEventListener("mouseleave", function() {
            menu_olt.style.display = "none";
        });
    } else {
       
    }
});

