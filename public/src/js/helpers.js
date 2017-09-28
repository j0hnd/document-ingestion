/**
 * get the language
 * @type {*|jQuery}
 * language is on js/lang
 */
var lang = $('meta[name=lang]').attr("content");

var Module = Module || {};

(function(mod , $) {

    mod.helpers  = {};
    mod.vars     = {};
    mod.formulas = {};

    mod.vars._apiURL = '/v1/api';

    /**
     * Generate token for csrf request
     * return token
     */
    mod.vars._token = $("meta[name='bruii_token']").attr('content');

    /**
     * Render underscore template
     * @param  {string}  template       template id
     * @param  {string}  element        append or insert element if null just return the template
     * @param  {object}  data           template variables
     * @param  {Boolean} isInsert       if false append if true empty children and append
     * @return {element}
     */
    mod.helpers.render = function(template, element, data, isInsert) {
        var tpl;

        var renderAppend = function(temp, element, data) {
            var template = _.template($(temp).html());

            if (!element) {
                return template(data);
            }

            return $(template).appendTo(element);

        };

        var renderInsert = function(template, element, data) {
            if (element) {
                $(element).children().remove();
            }
            return renderAppend(template, element, data);

        };

        if (!isInsert) {
            tpl = renderAppend(template, element, data);
        } else {
            tpl = renderInsert(template, element, data);
        }

        return tpl;
    };

    /**
     * Request Ajax Request
     * @param  {string}  type       	  GET,POST,DELETE,PUT & PATCH
     * @param  {string}  url        	  Route URL
     * @param  {object}  data       	  request datas
     * @param  {Boolean} cache      	  if caching is applicable
     * @param  {string}  dataType  		  json , html etc.
     * @param  {function}  beforeSend     statement before the success
     * @param  {function}  complete       process is complete
     * @param  {function}  done       	  process is success
     * @return {element}
     */
    mod.helpers.ajax = function(type , url , data , cache , dataType , beforeSend , complete , done) {
        $.ajax({
            type : type ,
            url  : url ,
            data : data,
            cache : cache ,
            dataType : dataType ,
            beforeSend : beforeSend ,
            complete : complete
        }).done(done).fail(function(jqxhr, settings, exception){

            var statuscode = jqxhr.status;
        });
    };

    /**
     * Return image loader
     * @param string element
     * @param int width
     * @param int height
     * @param string display
     */
    mod.helpers.attachedLoader = function(element , width , height , display) {
        if( display === "block") {
            $(element).html('<img class="bruii-loader" src="/images/preloader.gif" style="width:'+width+';height:'+height+';display:'+display+'">');
        } else {
            $(element).find('img.bruii-loader').hide();
        }
    };

    /**
     * Prevent Default
     * @param event
     * @returns {*}
     */
    mod.helpers.preventDefault = function(event) {
        return event.preventDefault();
    };


})(Module, jQuery);