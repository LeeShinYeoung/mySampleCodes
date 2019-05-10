/* 190122 이신영  판매자 평점 별점으로 변경*/
/**
 * JS
 * var init = new StarGradePoint({selectWrap : '[.class_name]'});
 *
 * HTML
 * <span class="[.class_name]" data-score="[0 ~ 5]" data-size="[n]px" data-letter-space="[n]px"></span>
 * HTML ATTRIBUTES
 * data-score : 0~5 *
 * data-class-none : class_name
 * data-class-half : class_name
 * data-class-full : class_name
 * data-color : hexColor
 * data-size : npx
 * data-letter-space : npx
 */
var StarGradePoint = function(data)
{
    this.allElement = document.querySelectorAll(data.selectWrap);
    for (var i=0; i<this.allElement.length; i++) this.init(i);
}
StarGradePoint.prototype.init = function(index)
{
    this.wrap = this.allElement[index];
    var maxScore = this.wrap.getAttribute('data-maxScore');
    var score = this.wrap.getAttribute('data-score');
    var fill_none = this.wrap.getAttribute('data-class-none');
    var fill_half = this.wrap.getAttribute('data-class-half');
    var fill_full = this.wrap.getAttribute('data-class-full');
    var color = this.wrap.getAttribute('data-color');
    var size = this.wrap.getAttribute('data-size');
    var letter_space = this.wrap.getAttribute('data-letter-space');

    this.maxScore = (maxScore) ? maxScore : 5;
    this.score = (score) ? Math.round(score * 100) / 100 : 0;
    this.fill_none = (fill_none) ? fill_none : 'far fa-star';
    this.fill_half = (fill_half) ? fill_half : 'fas fa-star-half-alt';
    this.fill_full = (fill_full) ? fill_full : 'fas fa-star';
    this.color = (color) ? color : '#ec600a';
    this.size = (size) ? size : '20px';
    this.letter_space = (letter_space) ? letter_space : '-3';
    this.makeStar();
    this.fillStar();
    this.style();
}
StarGradePoint.prototype.makeStar = function()
{
    var star_wrap = document.createElement('span');
    for (var i=0; i<this.maxScore; i++) {
        var star = document.createElement('i');
        star_wrap.appendChild(star);
    }
    this.wrap.appendChild(star_wrap);
    this.starWrap = this.wrap.children[0];
}
StarGradePoint.prototype.fillStar = function()
{
    var grade = this.score;
    var range = 0.25;  // ( 별 채우는 단위가 0, 0.5, 1 일 경우 0.25 )
    var star_cnt = 1;
    for (var i=0; i<this.maxScore; i++) { //별 갯수만큼 반복 // 5
        var star = this.starWrap.children[i];
        star.className = this.fill_none; // 빈별로 초기화
        if (grade > star_cnt - (range * 3)) star.className = this.fill_half; // n.25보다 크면 일단 반쪽 채움 ( range가 0.25일 경우 )
        if (grade > star_cnt - range) star.className = this.fill_full; // n.75보다 크면 모두 채움
        star_cnt++;
    }
}
StarGradePoint.prototype.style = function()
{
    // this.wrap.style.fontSize = this.size;
    this.wrap.style.color = this.color;
    for (var i=0; i<this.starWrap.childElementCount; i++) {
        this.starWrap.children[i].style.letterSpacing = this.letter_space;
        this.starWrap.children[i].style.fontSize = this.size;
    }
}
