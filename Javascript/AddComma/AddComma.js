/*
window.onload = function() {
    new AddComma({
        tag_list : ['input[name=input_01]'
            , 'input[name=input_02]'
            , 'input[name=input_03]'
            , 'input[name=input_04]'
            , 'input[name=input_05]'
            , 'textarea[name=textarea_01]'
            , 'input[name=test]'
            , '.p']
    });
}
*/
AddComma = function(param)
{
    this.submit_clear = (param.submit_clear) ? false : true;
    var all_list = param.tag_list;

    this.form_list = [];
    this.use_list = [];
    for (var i=0; i<all_list.length; i++) {
        var obj = document.querySelector(all_list[i]);
        if (!obj) continue;
        if (obj.form && this.submit_clear) this.form_list.push(obj.form);
        this.use_list.push(obj);
        this.init(obj);
    }

    if (this.submit_clear) {
        this.form_list = this.form_list.filter((val, idx, arr) => arr.indexOf(val) === idx); // 중복 form 제거
        for (var i=0; i<this.form_list.length; i++)
            this.form_list[i].addEventListener('submit', this.clearAll.bind(this));
    }
}
AddComma.prototype.init = function(obj)
{
    this.AddOrClear(obj, 'add');
    obj.addEventListener('keyup', function(e) {
        this.AddOrClear(e.target, 'add');
    }.bind(this));
}
AddComma.prototype.AddOrClear = function(obj, type)
{
    this.clear = (type == 'clear') ? true : false;
    switch (obj.tagName) {
        case 'input':
        case 'INPUT':
        case 'textarea':
        case 'TEXTAREA':
            obj.value = this.regexComma(obj.value);
            break;
        default:
            obj.textContent = this.regexComma(obj.textContent);
            break;
    }
}
AddComma.prototype.regexComma = function(text)
{
    text = text.replace(/\,/gi,'');
    if (this.clear) return text;
    return text.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
AddComma.prototype.clearAll = function()
{
    for (var i=0; i<this.use_list.length; i++) {
        var obj = this.use_list[i];
        if (!obj) continue;
        this.AddOrClear(obj, 'clear');
    }
}