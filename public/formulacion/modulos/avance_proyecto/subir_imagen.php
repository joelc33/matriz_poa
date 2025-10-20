<script type="text/Javascript">
Ext.ns("upload");
upload.main = {
init: function(){
            //<IMAGENES DE vehiculo>
                             this.codigo;
                             this.botonEliminar = new Ext.Button({
                                 text: 'Eliminar Imagen',
                                 iconCls: 'icon-cancelar',
                                 handler: function(){
                                     Ext.MessageBox.confirm('Alerta', '¿Está seguro que desea eliminar la imagen?', function(btn){
                                         
                                         if(btn=='yes'){
                                           Ext.Ajax.request({
						url:'formulacion/modulos/avance_proyecto/funcion.php',
						parametro:'si',
						params:'co_img_avance='+upload.main.codigo+'&op=7'
						});
                                           avanceEditarPR.main.storeDatosImagenes.load();
                                           upload.main.botonEliminar.setDisabled(true);
                                         }
                                     });
                                 }
                             });

                             this.botonEliminar.setDisabled(true);
                             this.gridImagen = new Ext.grid.GridPanel({
//                                title: 'Imagenes',
                                height:320,
                                width:450,border:false,
                                autoScroll:true,
                                tbar:[
                                    this.botonEliminar
                                ],
                                store:avanceEditarPR.main.storeDatosImagenes,
                                columns: [
                                      {width: 350,height:200,sortable: true, renderer: avanceEditarPR.main.imagen,dataIndex: 'co_img_avance'}
                                ],
                                sm: new Ext.grid.RowSelectionModel({
                                     singleSelect: true,
                                     //AQUI ES DONDE ESTA EL LISTENER
                                    listeners: {
                                            rowselect: function(sm, row, rec) {
                                               upload.main.codigo = rec.json.co_img_avance;
                                               upload.main.botonEliminar.setDisabled(false);
                                            }
                                     }
                                }),
                                bbar: new Ext.PagingToolbar({
                                        pageSize: 1,
                                        store: avanceEditarPR.main.storeDatosImagenes
                                })
                            });

                             this.botonAdjuntar = new Ext.Button({
                                 text: 'Adjuntar Imagen',
                                 iconCls: 'icon-adjuntar',
                                 handler: function(){
                                    upload.main.addFileInput();
                                 }
                             });

                             this.panelvehiculo = new Ext.Panel({
                //                 title: 'Carga de Imagenes',
                                 id:'panel_vehiculo',
                                 tbar:{items:[this.botonAdjuntar]},
                                 height:294,border:false,
                                 html:'<div id="moreUploads" ></div></div> <div id="moreLink" ></div>'
                             });

                             this.panelImagenes = new Ext.Panel({
//                                title: 'Imagenes Cargadas',
				border:false,
                                items:[this.gridImagen]
                             });

                              this.fielsetImagen =  new Ext.form.FieldSet({
                                    title:'',border:false,
                                    items:[
                                            {
                                            layout:'column',
                                            defaults:{layout:'form',labelAlign:'top'},border:false,
                                            items:[
                                                {   columnWidth:.7,border:false,
                                                    items:[
                                                        this.panelImagenes
                                                    ]
                                                },
                                                {
                                                    columnWidth:.3,border:false,
                                                    items:[
                                                        this.panelvehiculo
                                                    ]
                                                }
                                            ]
                                            }
                                    ]

                             });

                             this.PanelUpload = new Ext.Panel({
                                 title:'Carga de Imagenes',border:false,
                                 items:[this.fielsetImagen]
                             });
        },
         addFileInput: function() {
            var d = document.createElement("div");
            avanceEditarPR.main.upload_number++;
            var file = document.createElement("input");
            file.setAttribute("type", "file");
            file.setAttribute("class","x-form-file")
            file.setAttribute("name", "adjunto[archivo"+avanceEditarPR.main.upload_number+"]");
            file.setAttribute("id", "adjunto[archivo"+avanceEditarPR.main.upload_number+"]");
            d.setAttribute("id", "archivo"+avanceEditarPR.main.upload_number);
            d.appendChild(file);
            document.getElementById("moreUploads").appendChild(d);
        }
}
</script>
