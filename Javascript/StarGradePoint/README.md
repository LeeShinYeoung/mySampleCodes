# StarGradePoint.js

## Use to default (Require fontawesome 3.
```html
<script src="StarGradePotnt.js" type="text/javascript"></script>
<script>
    var init = new StarGradePoint({selectWrap : '.class_name'})
</script>
<span class=".class_name" data-score="3.4" data-maxScore="5">
```

## Attributes
|Name|Default value|Type|Explain|
| ---- | ---- | ---- | ---- |
|data-score|[Require]|Float number|a score between 0 ~ max score|
|data-maxScore|5|number|Max score|
|data-class-none|far fa-star|String(ClassName)|class of empty star|
|data-class-half|fas fa-star-half-alt|String(ClassName)|class of half-filled star|
|data-class-full|fas fa-star|String(ClassName)|class of full-filled star|
|data-color|#ec600a(orange)|String(Hex color)|color of star|
|data-size|20px|String(px)|size of star|
|data-letter-space|-3|String(px)|letter space|star spacing|
