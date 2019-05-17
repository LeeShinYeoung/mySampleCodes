# AddComma.js



## 사용법 예시

```html
<script src='AddComma.js' type='text/javascript'></script>
<script>
window.onload = function() {
    new AddComma({
        tag_list : ['input[name=input_01]', .'.max_price']
    });
}
</script>
<form>
    <input type='text' name='product_name'>
    <input type='text' name='price'><span class='max_price'>MAX PRICE : 3000</span>
    <button type='submit'>Submit</button>
</form>
```



## Parameter

| Name    | Default | Type    | Explain |
| ------------ | ---- | ------- | ------- |
| tag_list     | [Requird] | Array   | 콤마를 생성할 태그를 지정 |
| submit_clear | True | Boolean | 지정한 태그가 Form안에 포함되어 있을 경우 Submit시도시 콤마를 제거해서 전송할것인가에 대한 여부 |

