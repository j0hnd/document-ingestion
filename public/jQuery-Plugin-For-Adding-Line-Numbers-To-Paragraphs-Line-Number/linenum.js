/*
  Line Number v0.0.0 (2014-02-06)
  jquery plugin Framework by Yuanhai Shi
  Released under the MIT license
  More info: https://github.com/shiyuanhai/linenum.js
*/
"use strict";
(function(factory){
if (typeof define === 'function' && define.amd) {
    // AMD. Register module depending on jQuery using requirejs define.
    define(['jquery'], factory);
}else{
    // No AMD.
    factory(jQuery);
}
}(function($){
  $.fn.extend({

    linenum : function (options){
      var newElem,//new element contains line number and original contents
          lineElem,//line number span
          thisContents,//original contents span
          thisElm=this,
          options,//config object
          maxlines=100,//specify max line
          lnwidth="10%",//line num width
          cwidth="90%",//contents width
          format="0";//format of line number. must have one number 0 and optional symbols

      //only support element without any child but text    
      if(this.children().length > 0){
        throw "$.linenum - Element can only contain text (Limitation)";
      }

      //init options
      if(options && typeof options.maxlines != "undefined"){
        if(isNaN(options.maxlines)){
          throw "$.linenum - options.maxlines is invalid.";
        }else{
          maxlines = Number(options.maxlines);
        }
      }
      if(options && typeof options.format != "undefined"){
        if(typeof options.format != "string" || options.format.match(/^.*0.*$/g)==null ){
          throw "$.linenum - options.format is invalid.";
        }else{
          format = options.format;
        }
      }
      if(options && typeof options.lnwidth != "undefined"){
          lnwidth = options.lnwidth;
      }
      if(options && typeof options.cwidth != "undefined"){
          cwidth = options.cwidth;
      }

      //create new span to hold original contents
      thisContents = $("<span>");
      thisContents.append(thisElm.html()).css({'float':'left',width:cwidth});

      //create numbers of line number span using max line number.
      lineElem = $("<span>");
      lineElem.css({'float':'left','overflow':'hidden',width:lnwidth});
      for(var line=maxlines;line>0;line--){
        lineElem.append(format.replace('0',(maxlines-line+1))+"<br>");
      }

      newElem = thisElm.clone().empty();//clone original element except its contents
      newElem.append(lineElem).append(thisContents);
      
      thisElm.empty();
      thisElm.replaceWith(newElem);//replace origianl element
      lineElem.height(thisContents.height());//set height of line number based on contents height

      //update line numbers when window resize
      $(window).on('resize',function(){
        lineElem.height(thisContents.height());
      });
      return {
        newElem:newElem,
        linenumSpan:lineElem,
        contentsSpan:thisContents
      };
    }
  });
}));
