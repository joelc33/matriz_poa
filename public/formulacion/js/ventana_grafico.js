function abrir_ventana_grafico(geturl){
    var msg = Ext.get('centro');
        msg.load({
                url: geturl,
                scripts: true,
                text: 'Cargando...'
     });
}
