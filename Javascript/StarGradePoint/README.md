# StarGradePoint.js


## Use to Basic (Require font-awesome libray)
```html
<!--If you use a basic star, you need it.-->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
<script src="StarGradePotnt.js" type="text/javascript"></script>
<script>
    var init = new StarGradePoint({selectWrap : '.class_name'});
</script>
<span class=".class_name" data-score="3.4"></span>
```
### run


## Attributes
|Name|Default value|Type|Explain|
| ---- | ---- | ---- | ---- |
|data-score|[Require]|Float number|score between 0 ~ [max score]|
|data-maxScore|5|number|Max score|
|data-class-none|far fa-star|String(ClassName)|class name of empty star|
|data-class-half|fas fa-star-half-alt|String(ClassName)|class name of half-filled star|
|data-class-full|fas fa-star|String(ClassName)|class name of full-filled star|
|data-color|#ec600a(orange)|String(Hex color)|color of star|
|data-size|20px|String(px)|size of star|
|data-letter-space|-3|String(px)|letter space|star spacing|
