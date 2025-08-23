$(function() {
    $('.search_detail_tgl_btn').click(function(){
        var parent=$(this).parent();
        console.log(parent.children('.search_detail_tgl_status').val());
        if(parent.children('.search_detail_tgl_status').val()=='open') {
            parent.children('.search_detail_tgl_status').val('close');
            parent.children('.search_detail').css('display','none');
            parent.children('.search_detail_tgl_btn').val("▼ 詳細展開");
        } else {
            parent.children('.search_detail_tgl_status').val('open');
            parent.children('.search_detail').css('display','block');
            parent.children('.search_detail_tgl_btn').val("▲ 詳細格納");
        }
    });

    $('.age_reset_button').click(function(){
        age_reset();
        $('input[type=checkbox][name="is_generation_search"]').prop("checked", false);
    });
    $('.age_preset_3').click(function(){
        age_reset();
        $('input[type=checkbox][name="age[30]"]').prop("checked", true);
        $('input[type=checkbox][name="age[31]"]').prop("checked", true);
        $('input[type=checkbox][name="is_generation_search"]').prop("checked", false);
    });
    $('.age_preset_4').click(function(){
        age_reset();
        $('input[type=checkbox][name="age[41]"]').prop("checked", true);
        $('input[type=checkbox][name="age[31]"]').prop("checked", true);
        $('input[type=checkbox][name="is_generation_search"]').prop("checked", false);
    });
    $('.grade_juushou_tgl').click(function(){
        const tgt=$('input.grd_btn_g');
        var false_exists=grade_g_false_exists();
        if(false_exists){
            tgt.prop("checked", true);
        }else{
            tgt.prop("checked", false);
        }
    });
    $('.grade_open_tgl').click(function(){
        const tgt=$('input.grd_btn_g');
        const tgt2=$("input[type=checkbox][name=grade_op]");
        var false_exists=grade_g_false_exists();
        if(tgt2.prop("checked")==false){ false_exists=true; }
        if(false_exists){
            tgt.prop("checked", true);
            tgt2.prop("checked", true);
        }else{
            tgt.prop("checked", false);
            tgt2.prop("checked", false);
        }
    });
    $('.grade_jouken_tgl').click(function(){
        const tgt=$('input.grd_btn_jouken');
        var false_exists=false;
        if($("input.grd_btn_jouken[name=grade_1w]").prop("checked")==false){ false_exists=true; }
        if($("input.grd_btn_jouken[name=grade_2w]").prop("checked")==false){ false_exists=true; }
        if($("input.grd_btn_jouken[name=grade_3w]").prop("checked")==false){ false_exists=true; }
        if(false_exists){
            tgt.prop("checked", true);
        }else{
            tgt.prop("checked", false);
        }
    });
    $('.grade_reset_btn').click(function(){ grade_reset(); });
    $('input[type=checkbox][name="is_generation_search"]').click(function(){
        if($(this).prop("checked")===true){
            age_reset();
        }
    });
});
function grade_g_false_exists(){
    var false_exists=false;
    if($("input.grd_btn_g[name=grade_g1]").prop("checked")==false){ false_exists=true; }
    if($("input.grd_btn_g[name=grade_g2]").prop("checked")==false){ false_exists=true; }
    if($("input.grd_btn_g[name=grade_g3]").prop("checked")==false){ false_exists=true; }
    return false_exists;
}
function age_reset(){
    $('input.age_btn').prop("checked", false);
}
function grade_reset(){
    $('input[type=checkbox][name^="grade_"]').prop("checked", false);
}
function course_type_reset(){
    $('input[type=checkbox][name^="course_type_"]').prop("checked", false);
}
function reset_and_checked(reset_target_prefix,enable_target_suffix){
    $('input[type=checkbox][name^="'+reset_target_prefix+'"]').prop("checked", false);
    $('input[type=checkbox][name="'+reset_target_prefix+enable_target_suffix+'"]').prop("checked", true);
}
/**
 * name1で指定したチェックボックスがオンならname2で指定したチェックボックスをオフにする
 */
function uncheck_if_checked(name1, name2) {
  var $target1 = $('input[type=checkbox][name="'+name1+'"]');
  var $target2 = $('input[type=checkbox][name="'+name2+'"]');

  if ($target1.is(':checked')) {
    $target2.prop('checked', false);
  }
}
