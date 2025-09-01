define(['jquery'], function($){
  return function(){
    $(document).ready(function() {
      function getMatrix(rgbArray){
        return `${rgbArray[0]/255} 0 0 0 0 
                0 ${rgbArray[1]/255} 0 0 0 
                0 0 ${rgbArray[2]/255} 0 0 
                0 0 0 1 0`
      }

      let color = $('.popup-local-diagnostic .ufo-image-top').css("color");

      let colorsArray = color.substring(4, color.length-1)
         .replace(/ /g, '')
         .split(',');

      $(".popup-local-diagnostic #ufo-filter feColorMatrix").attr("values", getMatrix(colorsArray));
    });
  }
});