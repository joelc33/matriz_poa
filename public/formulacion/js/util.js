/*
 * Ext JS Library 2.0
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 *
 * http://extjs.com/license
 */

//Variables por defecto para mensajes y demás

var msgFailure="No se pudo establecer comunicaci&oacute;n con el Servidor.<br>Vuelva a intentarlo";


Ext.BLANK_IMAGE_URL = 'resources-ext/images/default/s.gif';


//Para crear un combo que cambia el estilo (css)
Ext.ux.ThemeCombo = Ext.extend(Ext.form.ComboBox, {
    // configurables
     themeBlueText: 'Ext Blue Theme'
    ,themeGrayText: 'Gray Theme'
    ,themeBlackText: 'Black Theme'
    ,themeOliveText: 'Olive Theme'
    ,themePurpleText: 'Purple Theme'
    ,themeDarkGrayText: 'Dark Gray Theme'
    ,themeSlateText: 'Slate Theme'
    ,themeVistaText: 'Vista Theme'
    ,themePeppermintText: 'Peppermint Theme'
    ,themeChocolateText: 'Chocolate Theme'
    ,selectThemeText: 'Select Theme'
    ,lazyRender:true
    ,lazyInit:true
    ,cssPath:'resources-ext/css/' // mind the trailing slash

    // {{{
    ,initComponent:function() {

        Ext.apply(this, {
            store: new Ext.data.SimpleStore({
                 fields: ['themeFile', 'themeName']
                ,data: [
                     ['xtheme-default.css', this.themeBlueText]
                    ,['xtheme-gray.css', this.themeGrayText]
                    ,['xtheme-darkgray.css', this.themeDarkGrayText]
                    ,['xtheme-black.css', this.themeBlackText]
                    ,['xtheme-olive.css', this.themeOliveText]
                    ,['xtheme-purple.css', this.themePurpleText]
                    ,['xtheme-slate.css', this.themeSlateText]
                    ,['xtheme-peppermint.css', this.themePeppermintText]
                    ,['xtheme-chocolate.css', this.themeChocolateText]
                ]
            })
            ,valueField: 'themeFile'
            ,displayField: 'themeName'
            ,triggerAction:'all'
            ,mode: 'local'
            ,forceSelection:true
            ,editable:false
            ,fieldLabel: this.selectThemeText
        }); // end of apply

        // call parent
        Ext.ux.ThemeCombo.superclass.initComponent.apply(this, arguments);
    } // end of function initComponent
    // }}}
    // {{{
    ,onSelect:function() {
        // call parent
        Ext.ux.ThemeCombo.superclass.onSelect.apply(this, arguments);

        // set theme
        var theme = this.getValue();
        Ext.util.CSS.swapStyleSheet('theme', this.cssPath + theme);

        if(Ext.state.Manager.getProvider()) {
            Ext.state.Manager.set('theme', theme);
        }
    } // end of function onSelect
    // }}}

}); // end of extend


Ext.utiles = function(){
    var msgCt;

    function createBox(t, s){
        return ['<div class="msg">',
                '<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>',
                '<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc"><h3>', t, '</h3>', s, '</div></div></div>',
                '<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>',
                '</div>'].join('');
    }
    return {
        msg : function(title, format){
            if(!msgCt){
                msgCt = Ext.DomHelper.insertFirst(document.body, {id:'msg-div'}, true);
            }
            msgCt.alignTo(document, 't-t');
            var s = String.format.apply(String, Array.prototype.slice.call(arguments, 1));
            var m = Ext.DomHelper.append(msgCt, {html:createBox(title, s)}, true);
            m.slideIn('t').pause(3).ghost("t", {remove:true});
        },

        init : function(){
            var t = Ext.get('exttheme');
            if(!t){ // run locally?
                return;
            }
            var theme = Cookies.get('exttheme') || 'aero';
            if(theme){
                t.dom.value = theme;
                Ext.getBody().addClass('x-'+theme);
            }
            t.on('change', function(){
                Cookies.set('exttheme', t.getValue());
                setTimeout(function(){
                    window.location.reload();
                }, 250);
            });

            var lb = Ext.get('lib-bar');
            if(lb){
                lb.show();
            }
        }
    };
}();

Ext.utiles.shortBogusMarkup = '<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed metus nibh, sodales a, porta at, vulputate eget, dui. Pellentesque ut nisl. Maecenas tortor turpis, interdum non, sodales non, iaculis ac, lacus. Vestibulum auctor, tortor quis iaculis malesuada, libero lectus bibendum purus, sit amet tincidunt quam turpis vel lacus. In pellentesque nisl non sem. Suspendisse nunc sem, pretium eget, cursus a, fringilla vel, urna.';
Ext.utiles.bogusMarkup = '<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed metus nibh, sodales a, porta at, vulputate eget, dui. Pellentesque ut nisl. Maecenas tortor turpis, interdum non, sodales non, iaculis ac, lacus. Vestibulum auctor, tortor quis iaculis malesuada, libero lectus bibendum purus, sit amet tincidunt quam turpis vel lacus. In pellentesque nisl non sem. Suspendisse nunc sem, pretium eget, cursus a, fringilla vel, urna.<br/><br/>Aliquam commodo ullamcorper erat. Nullam vel justo in neque porttitor laoreet. Aenean lacus dui, consequat eu, adipiscing eget, nonummy non, nisi. Morbi nunc est, dignissim non, ornare sed, luctus eu, massa. Vivamus eget quam. Vivamus tincidunt diam nec urna. Curabitur velit.</p>';

//Espacio para vTypes añaidos
Ext.form.VTypes["numericVal"] = /^[0-9]+$/;
Ext.form.VTypes["numeric"]=function(v){
 return Ext.form.VTypes["numericVal"].test(v);
}
Ext.form.VTypes["numericText"]="S&oacute;lo se admiten valores n&uacute;mericos";
Ext.form.VTypes["numericMask"]=/[0-9]/;

Ext.form.VTypes["timeVal"] = /^([0-9]|[0-9][0-9]):([0-5][0-9])$/i;
Ext.form.VTypes["time"]=function(v){
 return Ext.form.VTypes["timeVal"].test(v);
}
Ext.form.VTypes["timeMask"] = /[\d\s:]/i;
Ext.form.VTypes["timeText"] = 'No es una hora valida. Debe tener el formato "00:00 - 23:59".';

//Fin de espacio para vTypes añadidos

// old school cookie functions grabbed off the web
var Cookies = {};
Cookies.set = function(name, value){
     var argv = arguments;
     var argc = arguments.length;
     var expires = (argc > 2) ? argv[2] : null;
     var path = (argc > 3) ? argv[3] : '/';
     var domain = (argc > 4) ? argv[4] : null;
     var secure = (argc > 5) ? argv[5] : false;

     document.cookie = name + "=" + escape (value) +
       ((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
       ((path == null) ? "" : ("; path=" + path)) +
       ((domain == null) ? "" : ("; domain=" + domain)) +
       ((secure == true) ? "; secure" : "");

};

Cookies.get = function(name){
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	var j = 0;
	while(i < clen){
		j = i + alen;
		if (document.cookie.substring(i, j) == arg)
			return Cookies.getCookieVal(j);
		i = document.cookie.indexOf(" ", i) + 1;
		if(i == 0)
			break;
	}
	return null;
};

Cookies.clear = function(name) {
  if(Cookies.get(name)){
    document.cookie = name + "=" +
    "; expires=Thu, 01-Jan-70 00:00:01 GMT";
  }
};

Cookies.getCookieVal = function(offset){
   var endstr = document.cookie.indexOf(";", offset);
   if(endstr == -1){
       endstr = document.cookie.length;
   }
   return unescape(document.cookie.substring(offset, endstr));
};


function doJSON(stringData) {
	try {
		stringData = stringData.split('\r').join('\\r');
		stringData = stringData.split('\n').join('\\n');
		var jsonData = Ext.util.JSON.decode(stringData);
		return jsonData;
	}
	catch (err) {
		//Ext.MessageBox.alert('ERROR', 'No es posible interpretar los datos recibidos.<br>Vuelva a intentarlo' + stringData);
		//Variables de la excepcion serian, err.message, err.description
		Ext.MessageBox.alert('ERROR', 'No es posible interpretar los datos recibidos.<br>Vuelva a intentarlo. '+err.description);
	}
}

//Método utilizado para pasar una cadena de texto a HTML y sustituir los \n por <br>
function Text2Html(cadena){
	var result = Ext.util.Format.htmlEncode(cadena);
	result = cadena.split('\n').join('<br/>');
	return result;
}

//Método utlilizado para obtener el parametro con clave determinada de entre una array de parametros
function getParametro(array, clave){
	for(i=0; i<array.length; i++){
		var param=array[i];
		if(param.clave==clave)
			return param;
	}
	return null;
}

//Cargando
/*setTimeout(function(){
		Ext.get('loading').remove();
        Ext.get('loading-mask').fadeOut({remove:true});
}, 250);*/
