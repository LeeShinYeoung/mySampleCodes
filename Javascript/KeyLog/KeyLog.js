/**
 * KeyLog실행시 해당 페이지에서 눌리는 모든 키보드값을 keyLog라는 변수에 저장한다
 * 임의로 지정한 리스트 혹은 input태그는 keyLog변수안에 별도로 영역을 생성하여 저장한다
 *
 *     window.onload = function() {
            var list = [
                'brand_name',
                'product_name',
                'product_sangtae',
                'jaego',
            ]
            new KeyLog({
                list : list,
                parent : '.product_add'
            });
        }
 *
 *
 * data = {}
 * 태그의 name 배열
 * data.list = array();
 * ex) <input type='text' name='id'>... => data.list = ['id',...]
 *
 * 부모태그
 * data.parent = .class, #id
 * ex) <form class='reg'> => data.parent = '.reg'
 */
var KeyLog = function(data)
{
    this.parent = (data.parent) ? document.querySelector(data.parent) : document.body;
    //this.submit = (data.submit) ? document.querySelector(data.submit) : null;
    var collection_input = this.parent.getElementsByTagName('input');
    var collection_textarea = this.parent.getElementsByTagName('textarea');
    this.list = [];
    for (var i=0,end=collection_input.length; i<end; ++i) this.list.push(collection_input[i]);
    for (var i=0,end=collection_textarea.length; i<end; ++i) this.list.push(collection_textarea[i]);

    this.log = [];
    this.execTime = (new Date).getTime();
    this.bindEvent();
};
KeyLog.prototype.bindEvent = function()
{
    //ONLOAD
    var log = {};
    log.type = 'onload';
    log.value = null;
    log.time = this.getTimeDiff();
    this.log.push(log);
    // MOUSE
    document.body.addEventListener('click',this.onMouseClick.bind(this));
    // FOCUS
    for (var i=0,end=this.list.length; i<end; ++i) {
        this.list[i].addEventListener('focusin',this.onFocusIn.bind(this));
        this.list[i].addEventListener('focusout',this.onFocusOut.bind(this));
    }
    // KEY
    for (var i=0,end=this.list.length; i<end; ++i) {
        if (this.list[i].tagName.toLowerCase() === 'textarea' || this.list[i].type.toLowerCase() === 'text') {
            this.list[i].addEventListener('keydown',this.onKeyDown.bind(this));
            this.list[i].addEventListener('keyup',this.onKeyUp.bind(this));
        }
    }
    // SUBMIT
    if (this.parent.tagName.toLowerCase() == 'form')
        this.parent.addEventListener('submit',this.onSubmit.bind(this));
};
KeyLog.prototype.onMouseClick = function(e) {
    var log = {};
    log.type = 'click';
    log.value = (e.target.name) ? e.target.name : null;
    log.time = this.getTimeDiff();
    this.log.push(log);
};
KeyLog.prototype.onFocusIn = function(e)
{
    var log = {};
    log.type = 'focusIn';
    log.value = e.target.name;
    log.time = this.getTimeDiff();
    var tag_name = e.target.tagName.toUpperCase();
    switch (tag_name) {
        case 'INPUT': log.focus_data = e.target.value; break;
        case 'TEXTAREA': log.focus_length = e.target.value.length; break;
    }
    log.tag = tag_name;
    this.log.push(log);
};
KeyLog.prototype.onFocusOut = function(e)
{
    var log = {};
    log.type = 'focusOut';
    log.value = e.target.name;
    log.time = this.getTimeDiff();
    var tag_name = e.target.tagName.toUpperCase();
    switch (tag_name) {
        case 'INPUT': log.focus_data = e.target.value; break;
        case 'TEXTAREA': log.focus_length = e.target.value.length; break;
    }
    log.tag = tag_name;
    this.log.push(log);
};
KeyLog.prototype.onKeyDown = function(e)
{
    var log = {};
    log.type = 'keyDown';
    log.value = (e.key === 'Process') ? e.code.replace('Key', '').toLowerCase() : e.key.toLowerCase();
    log.time = this.getTimeDiff();
    this.log.push(log);
};
KeyLog.prototype.onKeyUp = function(e)
{
    var log = {};
    log.type = 'keyUp';
    if(['Control','Alt','Shift'].indexOf(e.key) > -1) {
        log.value = e.key.toLowerCase();
        log.time = this.getTimeDiff();
        this.log.push(log);
    }
};
KeyLog.prototype.getTimeDiff = function()
{
    return ((new Date).getTime() - this.execTime)/1000;
}
KeyLog.prototype.onSubmit = function()
{
    if (this.textarea) {
        this.textarea.textContent = JSON.stringify(this.log);
    } else {
        var textarea = document.createElement('textarea');
        textarea.name = 'keyLog';
        textarea.textContent = JSON.stringify(this.log);
        textarea.style.display = 'none';
        this.textarea = this.parent.appendChild(textarea);
    }
    var log = {};
    log.type = 'submit';
    log.time = this.getTimeDiff();
    this.log.push(log);
}

