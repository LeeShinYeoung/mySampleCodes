/**
 window.onload = function() {
                    new KeyLogAnlz({
                        data : [JsonData]
                        ,emptyIgnore : [true] || [false]
                        ,parent : [.class, #id]
                    });
                }
 **/
var KeyLogAnlz = function(param)
{
    this.emptyIgnore = (param.emptyIgnore) ? true : false; //키 이벤트가 일어난 포커스만 보여줄것인지 선택 (기본값 : 다보여줌)
    this.parent = (param.parent) ? document.querySelector(param.parent) : null; //xmp 출력 부모태그
    for (var i=0; i<param.data.length; i++) this.init(param.data[i]);
}
KeyLogAnlz.prototype.init = function(data) {
    var data = JSON.parse(data.replace(/[\']/g, '"'));


    this.click = [];
    this.keyboard = [];

    for (var i=0; i<data.length; i++) { //전체배열중 '키', '마우스' 이벤트 분리
        var row = data[i];
        if (row.type == 'click') this.click.push(row);
        else this.keyboard.push(row);
    }

    this.anlzClick();
    this.anlzKeyboard();
}
KeyLogAnlz.prototype.anlzClick = function() {}
KeyLogAnlz.prototype.anlzKeyboard = function()
{
    // 순서
    // 1. makeLogtoArr() 포커스, 키 구분없는 Json형태 데이터를 포커스로 구분해서 3차원배열로 변환
    // 2. trimArr() 배열로된 글자들을 한 문자열로 합침
    // 3. makeText() 배열 -> 편하게 볼수있는 텍스트로 변환
    // [선택] showXmp() 화면에 this.txt 출력
    // [선택] getText() this.txt 반환

    this.makeLogToArr(); //this.keyArr가 생성됨
    //console.log(this.keyArr);
    this.trimArr(); //this.keyArr가 정리됨
    //console.log(this.keyArr);
    this.makeText(); //this.keyArr를 토대로 this.txt가 생성됨
    //console.log(this.txt);
    if (this.parent) this.showXmp();
}
KeyLogAnlz.prototype.makeLogToArr = function() // 포커스, 키 구분없는 Json형태 데이터를 포커스로 구분해서 3차원배열로 변환
{
    this.keyArr = [];
    this.nowFocus = null; //depth 1
    this.modIndex = null; //depth 2
    for (var i=0; i<this.keyboard.length; i++) {
        var row = this.keyboard[i];
        try {
            switch (row.type) {
                case 'onload':
                    this.nowFocus = i;
                    var focus = [];
                    focus.name = row.type;
                    focus.time = row.time;
                    focus.data = '';//JSON.stringify(this.keyboard);
                    this.keyArr[this.nowFocus] = focus;
                    this.nowFocus = null;
                    break;
                case 'focusIn':
                    if (row.value == '') continue;
                    this.nowFocus = i;
                    this.focus = [];
                    this.focus.name = row.value;
                    this.focus.time = row.time;
                    this.focus.tag = row.tag;
                    this.focus.data = [];
                    this.focus.before_data = (row.focus_data) ? row.focus_data : row.focus_length;
                    this.keyArr[this.nowFocus] = this.focus;
                    break;
                case 'focusOut':
                    this.focus.after_data = (row.focus_data) ? row.focus_data : row.focus_length;
                    this.keyArr[this.nowFocus] = this.focus;
                    this.nowFocus = null;
                    break;
                case 'keyDown':
                    var focus_wrap = this.keyArr[this.nowFocus].data;
                    if (['control','shift','alt'].indexOf(row.value) > -1) {
                        var modifier = [];
                        modifier.name = this.replaceKey(row.value);
                        modifier.data = [];
                        var mod = this.keyArr[this.nowFocus].data.push(modifier);
                        this.modIndex = mod - 1;
                    } else {
                        if (this.modIndex != null) focus_wrap[this.modIndex].data.push(this.replaceKey(row.value));
                        else focus_wrap.push(this.replaceKey(row.value));
                    }
                    break;
                case 'keyUp':
                    if (['control','shift','alt'].indexOf(row.value) > -1) this.modIndex = null;
                    break;
            }
        } catch (e) {
            console.log(e);
            console.log(row);
        }
    }
    this.keyArr = this.keyArr.filter(n => n);

    if (this.emptyIgnore) { //데이터 없는 포커스를 무시?
        for (var i=this.keyArr.length-1; i>=0; i--) //splice 때문에 역순으로 반복
            if (this.keyArr[i].data.length == 0) this.keyArr.splice(i, 1);
    }
    return this.keyArr;
}
KeyLogAnlz.prototype.trimArr = function() // 하나하나 배열로된 글자들을 한줄로 합침
{
    var mergeKey = function(focusData) {
        var txt_row = '';
        var rtn = [];
        for (var i=0; i<focusData.data.length; i++) { //depth 1 일반키 처리
            var key = focusData.data[i];
            if (Array.isArray(key)) { //depth 2 > 수정키 처리
                rtn.push(txt_row);
                txt_row = '';
                txt_row += key.name+' {';
                for (var j=0; j<key.data.length; j++) txt_row += key.data[j];
                txt_row += '}';
                rtn.push(txt_row);
                txt_row = '';
            } else {
                txt_row += key;
            }
        }
        if (txt_row) rtn.push(txt_row);
        return rtn;
    }.bind(this);

    for (var i=0; i<this.keyArr.length; i++) {
        var focus = this.keyArr[i];
        var focus_data = mergeKey(focus);
        focus.data = focus_data;
    }
}
KeyLogAnlz.prototype.makeText = function() //배열 -> 편하게 볼수있는 텍스트
{
    this.txt = '';
    for (var i=0; i<this.keyArr.length; i++) {
        var focus = this.keyArr[i];
        var history = (focus.tag == 'INPUT' || focus.tag == 'TEXTAREA') ? true : false;
        this.txt += focus.name+' '+focus.time+' {\n';
        if (history) this.txt += '\t<< BEFORE : '+focus.before_data+'\n';
        if (history) this.txt += '\t<< AFTER : '+focus.after_data+'\n';
        for (var j=0; j<focus.data.length; j++) {
            var row = focus.data[j];
            this.txt += '\t'+row+'\n';
        }
        this.txt += '}\n';
    }
}
KeyLogAnlz.prototype.replaceKey = function(value) // 기능키정의
{
    value = value.toLowerCase();
    var func = '';
    switch (value) {
        case 'left':
        case 'arrowleft': func = '<'; break;
        case 'right':
        case 'arrowright': func = '>'; break;
        case 'up':
        case 'arrowup': func = '^'; break;
        case 'down':
        case 'arrowdown': func = '↓'; break;
        case 'control': func = 'Ctrl'; break;
        case 'backspace': func = '[B]'; break;
        case 'semicolon': func = ';'; break;
        case 'hangulmode':
        case 'lang1': func = '[HY]'; break;
        case 'space':
        case 'spacebar': func = '[ ]'; break;
        case 'decimal': func = '.'; break;
        case 'alt':
        case 'shift':
        case 'tab':
        case 'capslock':
        case 'home':
        case 'insert':
        case 'delete':
        case 'enter': func = value; break;
    }
    value = (func) ? func.toUpperCase() : value;
    return value;
}
KeyLogAnlz.prototype.showXmp = function()
{
    this.parent = (this.parent) ? this.parent : document.body;
    var xmp = document.createElement('xmp');
    xmp.textContent = this.txt;
    xmp.style.margin = '50px 0';
    xmp.style.padding = '30px';
    xmp.style.backgroundColor = 'rgb(66, 66, 66)';
    xmp.style.color = '#fff';
    this.parent.appendChild(xmp);
}
KeyLogAnlz.prototype.getText = function()
{
    return this.txt;
}