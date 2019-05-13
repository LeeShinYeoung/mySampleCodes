# StarGradePoint.js


## 기본 사용법
```html
<!-- 기본 별모양은 font-awesome을 사용합니다 -->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
<script src="StarGradePotnt.js" type="text/javascript"></script>
<script>
    var init = new StarGradePoint({selectWrap : '.class_name'});
</script>
<span class=".class_name" data-score="3.4"></span>
```
### 실행
#### score = 3.4, maxScore = 5
![score_34](./images/score_34.PNG)

## HTML 속성
|Name|Default value|Type|Explain|
| ---- | ---- | ---- | ---- |
|data-score|[Require]|Float number|0 ~ [maxScore] 사이의 실수|
|data-maxScore|5|number|최대 점수 (별갯수)|
|data-class-none|far fa-star|String(ClassName)|비어있는 별의 class 이름|
|data-class-half|fas fa-star-half-alt|String(ClassName)|절반 채워진 별의 class 이름|
|data-class-full|fas fa-star|String(ClassName)|모두 채워진 별의 class 이름|
|data-color|#ec600a(orange)|String(Hex color)|별 색상|
|data-size|20px|String(px)|별 크기|
|data-letter-space|-3|String(px)|별 간격|
