var LoadingBarmin = '<div style="padding:2px;text-align:center;"><img style="height:9px;" src="../style/img/'+loadingImage+'" /></div>';
$(function(){
  $('#scheduler_button').on('click', function(){
    let oltid = $(this).data('olt');
    let onuid = $(this).data('onu');
    $('#popup_content').html(LoadingBarmin);
    $('#scheduler_popup, #popup_overlay').show();
    $.post(root + '?do=scheduler&act=get', {oltid:oltid,onuid:onuid}, function(response){
      $('#popup_content').html(response);
      $('.template-item').on('click', function(){
        let tid = $(this).data('template-id');
        let oltid = $(this).data('olt');
        let onuid = $(this).data('onu');
        $('#popup_content').html(LoadingBarmin);
        $.post(root + '?do=scheduler&act=load',{tid:tid,oltid:oltid,onuid:onuid}, function(commandsHtml){
          $('#popup_content').html(commandsHtml);
        });
      });
    });
  });  
  $('#photo_onu_button').on('click', function(){
    let oltid = $(this).data('olt');
    let onuid = $(this).data('onu');
    $('#popup_content').html(LoadingBarmin);
    $('#scheduler_popup, #popup_overlay').show();
    $.post(root + '?do=scheduler&act=get', {oltid:oltid,onuid:onuid}, function(response){
      $('#popup_content').html(response);
      $('.template-item').on('click', function(){
        let onuid = $(this).data('onu');
        $('#popup_content').html(LoadingBarmin);
        $.post(root + '?do=scheduler&act=load',{tid:tid,oltid:oltid,onuid:onuid}, function(commandsHtml){
          $('#popup_content').html(commandsHtml);
        });
      });
    });
  });
  $('#popup_close, #popup_overlay').on('click', function(){
    $('#scheduler_popup, #popup_overlay').hide();
  });
});
