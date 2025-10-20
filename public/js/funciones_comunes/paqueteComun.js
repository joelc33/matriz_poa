Ext.ns("paqueteComunJS");
Ext.SSL_SECURE_URL="images/default/s.gif";
Ext.BLANK_IMAGE_URL = 'images/default/s.gif';
paqueteComunJS.funcion = {
    /**
     * Esta funcion permite Cargar un combo de un modulo
     */
    cargarComboStoreBy:function(opcion){
        var ObjStore = new Ext.data.JsonStore({
                url:opcion.url+'/'+opcion.accion,
		root:'data',
		fields: ['value','label']
        });
        return ObjStore;
    },
    mostrarVentana: function(opcion){
        ruta = opcion.url;
        this.msg = Ext.get('formulario_ubicacion');
        if(opcion.parametro=='si'){
            this.msg.load({
             url: opcion.url,
             scripts: true,
             params:'id='+opcion.pByCod,
             text: "Cargando.."
            });
        }else{
            this.msg.load({
             url: opcion.url,
             waitMsg: 'Cargando..',
             scripts: true,
             text: "Cargando.."
            });
        }


        this.msg.show();

    },
    /**
     * Esta funcion permite cargar un combo a partir
     * de un <<valor>>.
     */
    seleccionComboBy: function(opcion){
        //{valor:,objetoField: , objetoStore:}
         opcion.objetoStore.on('load', function(){
                if(opcion.valor!=""){
                       opcion.objetoField.setValue(opcion.valor);
                       try{
                          objetoField.selectByValue(opcion.valor);
                       }
                       catch(err){

                       }


                }

      });
    },
    /**
     *  Esta funcion devuelve un objeto JSON.
     *  @param String stringData String de arreglo tipo json para ser deficado.
     *  Ejemplo:
     *  <code>
     *  doJSON({stringData:'{
     *      nombre  :   "usuario",
     *      correo  :   "personal[AT]persona.com"}'
     *  });
     *  </code>
     *  @return return OBJ-JSON
     */
    doJSON:function(opcion){
        try {
                //stringData = stringData.split('\r').join('\\r');
                //stringData = stringData.split('\n').join('\\n');
                var jsonData = Ext.util.JSON.decode(opcion.stringData);
                  return jsonData;
        }
        catch (err) {
                //Ext.MessageBox.alert('ERROR', 'No es posible interpretar los datos recibidos.<br>Vuelva a intentarlo' + stringData);
                //Variables de la excepcion serian, err.message, err.description
                Ext.MessageBox.alert('ERROR', 'No es posible interpretar los datos recibidos.<br>Vuelva a intentarlo. '+err.description);
        }
    },
    /**
     * Esta funcion valida si el obj cadena contiene valor o no
     * , de no contener valor, returna vacio.
     */
    verificarSiNull:function(opcion){
        if(opcion.cadena == null){
            return '';
        }else{
            return opcion.cadena;
        }
    },
/**
 * Esta funcion carga un combo, sus parametros son
 * @param obj objCMB Objeto del comboBox
 * @param string value Valor a buscar en el cmb y ser posicionado previamente
 * @param obj objStore Objeto store que validara el evento load
 * <code>
 * paqueteComunJS.funcion.seleccionarComboByCo({objCMB:vehiculo.mainVehiculo.cmbClasificacion,
 *                       value:vehiculo.mainVehiculo.OBJ.co_clase_veh
 *                       objStore:vehiculo.mainVehiculo.storeClasificacion});
 * </code>
 */
    seleccionarComboByCo: function(opcion){
        opcion.objStore.on("load",function(){
               valueOPT = opcion.value;
               objCmb   = opcion.objCMB;
               if(valueOPT!=''){
                    objCmb.setValue(valueOPT);
                    try{
                      objCmb.selectByValue(valueOPT);
                    }catch(err){};
               }
        });
    },
    /**
     * Esta funcion itera un store y devuelve la suma de la columna.
     * return sum.
     */
    getSumaColumnaGrid: function(opcion){
        var sum =0;
            opcion.store.each(function(record){
                    record.fields.each(function(field){
                        if(field.name==opcion.campo){
                            sum += parseFloat(record.get(field.name));
                        }
                });
            }, this);
        return sum.toFixed(2);
    },
    //paqueteComunJS.funcion.getSumaColumnaGrid
    getNumeroFormateado: function(num){
        num = num || '0';
        num = num.toString().replace(/\$|\,/g,'');
        if(isNaN(num))
        num = "0";
        sign = (num == (num = Math.abs(num)));
        num = Math.floor(num*100+0.50000000001);
        cents = num%100;
        num = Math.floor(num/100).toString();
        if(cents<10)
        cents = "0" + cents;
        for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
        num = num.substring(0,num.length-(4*i+3))+','+
        num.substring(num.length-(4*i+3));
        return (((sign)?'':'-') + 'Bs. ' + num + '.' + cents);
    },
    getJsonByObjStore: function(opcion){
            var json = '';
            opcion.store.each(function(store){
              json += Ext.util.JSON.encode(store.data) + ',';
            });
            json = json.substring(0, json.length - 1);
            return "["+json+"]";
    }

}//paqueteComunJS.funcion
function formatoNumero(val){
	    return paqueteComunJS.funcion.getNumeroFormateado(val);
return val;
}

function textoLargo(value, metadata) {
    metadata.attr = 'style="white-space: normal;"';
    return value;
}

function miCuenta(contenido){
	return ['<div class="msg">',
		'<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>',
		'<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc">',contenido,'</div></div></div>',
		'<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>',
		'</div>'].join('');
}

function abrir_ventana_grafico(geturl){
    var msg = Ext.get('grafico');
        msg.load({
                url: geturl,
                scripts: true,
                text: 'Cargando...'
     });
}

//Cargando
setTimeout(function(){
		Ext.get('loading').remove();
        Ext.get('loading-mask').fadeOut({remove:true});
}, 250);

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

//Crear Cookie
function createCookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}
//Leer Cookie
function readCookie(name) {
    var nameEQ = name + "=",
        ca = document.cookie.split(';'),
        i,
        c,
        len = ca.length;
    for ( i = 0; i < len; i++) {
        c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1, c.length);
        }
        if (c.indexOf(nameEQ) == 0) {
            return c.substring(nameEQ.length, c.length);
        }
    }
    return null;
}
//Declarar Variable Cookie
var cookie = readCookie("estilo");

//Para crear un combo que cambia el estilo (css)
Ext.ux.ThemeCombo = Ext.extend(Ext.form.ComboBox,{
    //Configurables
    themeBlueText: 'Azul',
    themeSlateText: 'Azul Oscuro',
    themeRojoText: 'Rojo',
    themeGrayExtendText: 'Gris suavizado',
    themeDarkGrayText: 'Gris Oscuro',
    themePurpuraText: 'Purpura',
    themeOlivaText: 'Oliva',
    themeVistaText: 'Vista',
    themeNegroText: 'Negro',
    selectThemeText: 'Selecciona Tema',
    lazyRender:true,
    lazyInit:true,
    cssPath:'css/', //directorio
    value:cookie?cookie:'xtheme-silverCherry.css',
    initComponent:function() {
	Ext.apply(this, {
	store: new Ext.data.SimpleStore({
		fields:['themeFile','themeName'],
		data:[
			['ext-all.min.css', this.themeBlueText],
			['xtheme-slate.css', this.themeSlateText],
			['xtheme-silverCherry.css', this.themeRojoText],
			['xtheme-gray-extend.css', this.themeGrayExtendText],
			['xtheme-darkgray.css', this.themeDarkGrayText],
			['xtheme-purple.css', this.themePurpuraText],
			['xtheme-olive.css', this.themeOlivaText],
			['xtheme-vista.css', this.themeVistaText],
			['xtheme-slickness.css', this.themeNegroText]
                ]
            }),
            valueField: 'themeFile',
            displayField: 'themeName',
            triggerAction:'all',
            themeVar:'apptheme',
            mode:'local',
            forceSelection:true,
            editable:false,
            fieldLabel: this.selectThemeText
        }); // end of apply
        // call parent
        Ext.ux.ThemeCombo.superclass.initComponent.apply(this, arguments);
        //this.setValue(Ext.state.Manager.get(this.themeVar) || 'xtheme-default.css');
    }, // end of function initComponent
	setValue:function(val) {
		Ext.ux.ThemeCombo.superclass.setValue.apply(this, arguments);
		// set theme
		Ext.util.CSS.swapStyleSheet(this.themeVar, this.cssPath + val);
		Ext.utiles.msg('Mensaje del Sistema', 'Interfaz Renderizada');
		createCookie("estilo", val, 365);
		if(Ext.state.Manager.getProvider()){
			Ext.state.Manager.set(this.themeVar, val);
		}
	} // eo function setValue
}); // end of extend
// register xtype
Ext.reg('themecombo', Ext.ux.ThemeCombo);

//componente del iframe
Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent,{
	onRender: function (ct, position){this.el = ct.createChild({tag:'iframe', id: 'cont', frameBorder:0, src:this.url});}
});

//Crea un nuevo tab en el tabpanel central
function addTab(id,title,url,type,iconCls,param,opciones)
{
	//alert(url);
	var open = !this.tabpanel.getItem(id);
	if (open)
	{
		switch (type)
		{
			case 'iframe':
			//Creamos un nuevo ifram y cargamos dentro la url
			var newPanel = new Ext.Panel({
			        id : id,
			        title: title,
			        loadScripts: true,
			        autoScroll: true,
				border:false,
			        closable: true,
			        iconCls:iconCls,
			        layout:'fit',
			        items: [new Ext.ux.IFrameComponent({ id: id, url: url, name: id})]
		      	});
		     	this.tabpanel.add(newPanel);
		      	this.tabpanel.setActiveTab(newPanel);
			this.panel_detalle.collapse();
			break;
			case 'load':
			//Cargamos la pestaña por ajax
        		var newPanel = new Ext.Panel({
              id : id,
              title: title,
              loadScripts: true,
              autoScroll: true,
              border:false,
              closable: true,
              iconCls:iconCls,
              layout:'fit',
              padding: opciones,
              autoLoad: {url: url, scripts: true, scope: this, params: param}
		      	});
		     	this.tabpanel.add(newPanel);
		     	this.tabpanel.setActiveTab(newPanel);
			this.panel_detalle.collapse();
			break;
			default:
				//Ext.example.msg('Click','You clicked on "Action 1".');
				this.tabpanel.setActiveTab(id);
				Ext.Msg.alert('Aviso','Seleccione un opcion valida')
				//alert("Seleccione una opcion");
			break;
		}
	}
	else {
		//Si ya tenemos la pestaña creada la seleccionaremos
		this.tabpanel.setActiveTab(id);
		this.panel_detalle.collapse();
	}
}

//Panel Collabsible
Ext.namespace("Ext.ux","Ext.ux.TDGi");Ext.ux.TDGi.BorderLayout=function(c){Ext.ux.TDGi.BorderLayout.superclass.constructor.call(this,c,this)};
Ext.extend(Ext.ux.TDGi.BorderLayout,Ext.layout.BorderLayout,{northTitleAdded:!1,southTitleAdded:!1,eastTitleAdded:!1,westTitleAdded:!1,doCollapsedTitle:function(c){function l(b,c){if("east"==b.region||"west"==b.region){if(Ext.isIE6||Ext.isIE7)"east"==b.region?Ext.get(b.collapsedEl.dom.firstChild).applyStyles({margin:"3px 3px 5px 3px"}):Ext.get(b.collapsedEl.dom.firstChild).applyStyles({margin:"3px auto 5px 3px"});return Ext.DomHelper.append(b.collapsedEl,c)}return Ext.DomHelper.insertFirst(b.collapsedEl,
c)}if(c.collapsedTitle)if("object"==typeof c.collapsedTitle){if("object"==typeof c.collapsedTitle.element){var b=c.collapsedTitle.element;b.style=b.style?b.style+"float: left;":"float: left;";l(c,b);return!0}}else{if("string"==typeof c.collapsedTitle)return b=c.collapsedTitle,l(c,b),!0;if("boolean"==typeof c.collapsedTitle&&!0==c.collapsedTitle){if("east"==c.region||"west"==c.region)if(Ext.isIE6||Ext.isIE7)b={tag:"div",style:"writing-mode: tb-rl; ",html:c.title};else{var b=0,h="";for(i=0;b<c.title.length;i++)h+=
c.title.substr(b,1)+"<br />",b++;b={tag:"div",style:"text-align: center;",html:h}}else b={tag:"div",html:"<b>"+c.title+"</b> - Click para mostrar",style:"float: left; margin:3px 0px 0px 5px"};l(c,b);return!0}}},onLayout:function(c,l){var b;if(!this.rendered){l.position();l.addClass("x-border-layout-ct");var h=c.items.items;b=[];for(var f=0,g=h.length;f<g;f++){var e=h[f],j=e.region;e.collapsed&&b.push(e);e.collapsed=!1;e.rendered||(e.cls=e.cls?e.cls+" x-border-panel":"x-border-panel",e.render(l,f));
this[j]="center"!=j&&e.split?new Ext.layout.BorderLayout.SplitRegion(this,e.initialConfig,j):new Ext.layout.BorderLayout.Region(this,e.initialConfig,j);this[j].render(l,e)}this.rendered=!0}e=l.getViewSize();if(20>e.width||20>e.height)b&&(this.restoreCollapsed=b);else{this.restoreCollapsed&&(b=this.restoreCollapsed,delete this.restoreCollapsed);var h=e.width,m=e.height,f=h,g=m,r=j=0,k=this.north,n=this.south,p=this.west,q=this.east,e=this.center;if(!e)throw"No center region defined in BorderLayout "+
c.id;if(k&&k.isVisible()){var d=k.getSize(),a=k.getMargins();d.width=h-(a.left+a.right);d.x=a.left;d.y=a.top;j=d.height+d.y+a.bottom;g-=j;k.applyLayout(d);"undefined"!=typeof k.collapsedEl&&(k.collapsedTitle&&!1==this.northTitleAdded)&&this.doCollapsedTitle(k)&&(this.northTitleAdded=!0)}n&&n.isVisible()&&(d=n.getSize(),a=n.getMargins(),d.width=h-(a.left+a.right),d.x=a.left,k=d.height+a.top+a.bottom,d.y=m-k+a.top,g-=k,n.applyLayout(d),"undefined"!=typeof n.collapsedEl&&(n.collapsedTitle&&!1==this.southTitleAdded)&&
this.doCollapsedTitle(n)&&(this.southTitleAdded=!0));p&&p.isVisible()&&(d=p.getSize(),a=p.getMargins(),d.height=g-(a.top+a.bottom),d.x=a.left,d.y=j+a.top,m=d.width+a.left+a.right,r+=m,f-=m,p.applyLayout(d),"undefined"!=typeof p.collapsedEl&&(p.collapsedTitle&&!1==this.westTitleAdded)&&this.doCollapsedTitle(p)&&(this.westTitleAdded=!0));q&&q.isVisible()&&(d=q.getSize(),a=q.getMargins(),d.height=g-(a.top+a.bottom),m=d.width+a.left+a.right,d.x=h-m+a.left,d.y=j+a.top,f-=m,q.applyLayout(d),"undefined"!=
typeof q.collapsedEl&&(q.collapsedTitle&&!1==this.eastTitleAdded)&&this.doCollapsedTitle(q)&&(this.eastTitleAdded=!0));a=e.getMargins();e.applyLayout({x:r+a.left,y:j+a.top,width:f-(a.left+a.right),height:g-(a.top+a.bottom)});if(b){f=0;for(g=b.length;f<g;f++)b[f].collapse(!1)}Ext.isIE&&Ext.isStrict&&l.repaint()}}});Ext.Container.LAYOUTS.border=Ext.ux.TDGi.BorderLayout;
