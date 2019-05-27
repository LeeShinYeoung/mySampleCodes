# KeyLog

## KeyLog.js

부모로 지정한 태그 내에서 눌리는 모든 키보드, 마우스 클릭값을 변수에 누적시키는 기능. input, textarea같은 경우 따로 구분가능합니다.

### 실행 EX)

```html
<script src="KeyLog.js" type="text/javascript"></script>
<script>
window.onload = function() {
    new KeyLog({parent : '.form'});
}
</script>
<form class='form'>
	<input type="text" name="brand_name">
	<input type="text" name="product_name">
	<input type="text" name="jaego">
	<textarea name="comment"></textarea>
</form>
```

input태그 name이 "product_name"인 태그안에서 결과적으로 test라고 입력시 log 변수에 담기는 데이터

```javascript
{type: "onload", value: null, time: 0}
, {type: "click", value: "product_name", time: 2.118}
, {type: "focusIn", value: "product_name", time: 2.145, focus_data: "", tag: "INPUT"}
, {type: "keyDown", value: "t", time: 2.489}
, {type: "keyDown", value: "e", time: 2.551}
, {type: "keyDown", value: "a", time: 2.634}
, {type: "keyDown", value: "Backspace", time: 2.692}
, {type: "keyDown", value: "s", time: 2.744}
, {type: "keyDown", value: "t", time: 2.837}
, {type: "focusOut", value: "product_name", time: 3.525, focus_data: "test", tag: "INPUT"}
```



## KeyLogAnlz.js

KeyLog.js 에서 쌓은 데이터를 보기 편리하도록 텍스트로 바꿔주는 기능

### Param

| name        | default    | type     | explain                                                      |
| ----------- | ---------- | -------- | ------------------------------------------------------------ |
| data        | [Required] | JSON     | 분석할 데이터                                                |
| emptyIgnore | false      | Boolean  | 포커스가 일어났지만 데이터가 입력되지 않은 경우도 보여질것인지 여부 |
| parent      | null       | Selector | xmp를 출력시킬 부모태그                                      |



### 실행 EX)

```javascript 
var Anlz = new KeyLogAnlz({
    data : [위에서 생성된 데이터]
});
var result = Anlz.getText();
console.log(result);
```

console.log를 확인해보면 다음과 같이 보여집니다


```javascript
product_name 3.525 {
    << BEFORE :
    << AFTER : test
    tea[<]st
}
```

