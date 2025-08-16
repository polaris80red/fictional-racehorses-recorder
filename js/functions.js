function copyToClipboard(elm){
    var val = $(elm).val();
    navigator.clipboard.writeText(val);
}
function clearElmVal(elm){
    $(elm).val('');
    checkRequiredElm(elm);
}
function confirmAndClearElmVal(elm, input_title) {
    if($(elm).val()===''){
        return;
    }
    var title = input_title || 'この項目';
    if (confirm(title + 'を消去しますか？')) {
        $(elm).val('');
    }
}
function setElmVal(elm,val){
    $(elm).val(val);
    checkRequiredElm(elm);
}
function copyElmVal(from,to){
    $(to).val($(from).val());
    checkRequiredElm(to);
}
function convertHankaku(elm){
    const input=$(elm);
    var value=input.val().trim();
    value = value.replace(/[\uFF01-\uFF5E]/g, function(char) {
        return String.fromCharCode(char.charCodeAt(0) - 0xFEE0);
    }).replace(/\u3000/g, ' ');
    input.val(value);
    return value;
}
// 必須フォームが空のときに色を変化させる
$(document).ready(function() {
    // 対象要素をキャッシュ
    var $targets = $('input.required, select.required');
    // 初期チェック
    checkRequired($targets);
    // 値変更時やフォーカスアウト時に再チェック
    $targets.on('change blur', function() {
        checkRequired($targets);
    });
});
function checkRequiredElm(elm) {
    var $targets = $(elm);
    checkRequired($targets);
}
function checkRequired($targets) {
    $targets.each(function() {
        if($(this).hasClass('required')){
            var val = $(this).val();
            if ($.trim(val) === '') {
                $(this).css('background-color', '#fff4e5');
            } else {
                $(this).css('background-color', '');
            }
        }
    });
}
