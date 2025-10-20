<style type="text/css">
#panelOpciones {
  margin:5px 5px 5px 5px;
}
#panelOpciones dd {
	cursor:pointer;
	float:left;
	height:100px;
	margin:5px 5px 5px 10px;
	width:300px;
	zoom:1;
}
#panelOpciones dd img {
	border: 1px solid #ddd;
	float:left;
	height:90px;
	margin:5px 0 0 5px;
	width:120px;
}

#panelOpciones dd div {
	float:left;
	margin-left:10px;
	width:160px;
}

#panelOpciones dd h4 {
	color:#555;
	font-size:11px;
	font-weight:bold;
}
#panelOpciones dd p {
	color:#777;
}
#panelOpciones dd.over {
	background: #F5FDE3 url(/images/sample-over.gif) no-repeat;
}

#panelOpciones .x-panel-body {
	background-color:#fff;
	border:1px solid;
	border-color:#fafafa #fafafa #fafafa #fafafa;
}
#claseicon-ct {
	border:1px solid;
	border-color:#dadada #ebebeb #ebebeb #dadada;
	padding:2px;
}

#panelOpciones h2 {
	border-bottom: 2px solid #99bbe8;
	cursor:pointer;
	padding-top:6px;
}
#panelOpciones h2 div {
	background:transparent url(images/default/grid/group-expand-sprite.gif) no-repeat 3px -47px;
	color:#3764a0;
	font:bold 11px Helvetica, Arial, sans-serif;
	padding:4px 4px 4px 17px;
}
#panelOpciones .collapsed h2 div {
	background-position: 3px 3px;
}
#panelOpciones .collapsed dl {
	display:none;
}
.x-window {
	text-align:left;
}
#panelOpciones dd h4 span.new-claseicon{
	color: red;
}

#panelOpciones dd h4 span.updated-claseicon{
	color: blue;
}
</style>
<script type="text/javascript">
Ext.ns('Ext.opciones');
(function() {

opcionPanel = Ext.extend(Ext.DataView, {
    autoHeight   : true,
    frame        : true,
    itemSelector : 'dd',
    overClass    : 'over',
    tpl          : new Ext.XTemplate(
        '<div id="claseicon-ct">',
            '<tpl for=".">',
            '<div><a name="{id}"></a><h2><div>{title}</div></h2>',
            '<dl>',
                '<tpl for="opciones">',
                    '<dd ext:url="{url}" ext:titulo="{text}" ext:icono="{iconCls}" ext:idreg="{id}"><img src="images/{icon}">',
                        '<div><h4>{text}',
                            '<tpl if="this.esNuevo(values.estatus)">',
                                '<span class="new-claseicon"> (Nuevo)</span>',
                            '</tpl>',
                            '<tpl if="this.esActualizado(values.estatus)">',
                                '<span class="updated-claseicon"> (Actualizado)</span>',
                            '</tpl>',
                            '<tpl if="this.esExperimental(values.estatus)">',
                                '<span class="new-claseicon"> (Experimental)</span>',
                            '</tpl>',
                        '</h4><p>{desc}</p></div>',
                    '</dd>',
                '</tpl>',
            '<div style="clear:left"></div></dl></div>',
            '</tpl>',
        '</div>', {
         esExperimental: function(estatus){
             return estatus == 'experimental';
         },
         esNuevo: function(estatus){
             return estatus == 'nuevo';
         },
         esActualizado: function(estatus){
             return estatus == 'actualizado';
         }
    }),

    onClick : function(e){
        var group = e.getTarget('h2', 3, true);
        if(group){
            group.up('div').toggleClass('collapsed');
        }else {
            var t = e.getTarget('dd', 5, true);
            if(t && !e.getTarget('a', 2)){
                var idreg = t.getAttributeNS('ext', 'idreg');
                var url = t.getAttributeNS('ext', 'url');
                var titulo = t.getAttributeNS('ext', 'titulo');
                var icono = t.getAttributeNS('ext', 'icono');
                bajar.load({
                    url: url
                });
            }
        }
        return opcionPanel.superclass.onClick.apply(this, arguments);
    }
});
Ext.opciones.opcionPanel = opcionPanel;
Ext.reg('opcionespanel', Ext.opciones.opcionPanel);
})();

Ext.ns("libroLista");
libroLista.main = {
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

this.catalogo = [{!! $iconos !!}];

for (var i = 0, c; c = this.catalogo[i]; i++) {
    c.id = 'claseicon-' + i;
}

this.store = new Ext.data.JsonStore({
    idProperty : 'id',
    fields     : ['id', 'title', 'opciones'],
    data       : this.catalogo
});

this.opcionPanel_ = new Ext.Panel({
    frame      : true,
    autoHeight : true,
    autoScroll : true,
    items      : new opcionPanel({
    store : this.store
    })
});

this.opcionPanel_.render("panelOpciones");
}
};
Ext.onReady(libroLista.main.init, libroLista.main);
</script>
<div id="panelOpciones"></div>
