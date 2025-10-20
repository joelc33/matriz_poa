(function(Ext, Reingsys, async, paqueteComunJS, opcionPlanificador) {
    Ext.define('AccionCentralizada.Tab', {
        extend: 'Ext.TabPanel',
        xtype: 'accion_centralizada',
        constructor: function(config) {
            var self = this;

            var forma = Ext.create(
                Ext.apply({
                    xtype: 'accion_centralizada_forma',
                    autoHeight: true,
                    autoWidth: true,
                    padre: self
                }, config.frm)
            );

            config = Ext.apply({
                autoHeight: true,
                autoWidth: true,
                enableTabScroll: true,
                activeTab: 0,
                items: [
                    forma
                ]
            }, config);

            this.callParent(arguments);
        }
    });

    Ext.define('AccionCentralizada.Forma', {
        extend: 'Ext.form.FormPanel',
        xtype: 'accion_centralizada_forma',
        constructor: function(config) {
            var self = this,
                ancho = 400;

            var ac = config.ac;

            this.store_accion = new Ext.data.JsonStore({
                proxy: new Ext.data.HttpProxy({
                    /*url: 'formulacion/modulos/accionCentralizada/orm.php/tipo/accion',*/
                    url: 'auxiliar/accion/tipo',
                    /*method: 'POST'*/
                }),
                /*baseParams: {
                    op: 1
                },*/
                root: 'data',
                fields: [
                    'id', {
                        name: 'nombre',
                        convert: function(v, r) {
                            return r.id + ' - ' + r.de_nombre;
                        }
                    },
		                'de_accion'
                ]
            });
            
	    this.accion_id = new Ext.form.ComboBox({
		fieldLabel:'1.2. TIPO DE ACCIÓN',
		store: this.store_accion,
		typeAhead: true,
		valueField: 'id',
		displayField:'nombre',
		hiddenName:'id_accion',
		forceSelection:true,
		resizable:true,
		triggerAction: 'all',
		emptyText: 'Seleccione el tipo de Acción Centralizada',
		selectOnFocus: true,
		mode: 'local',
		width:400,
		allowBlank:false,
		onSelect: function(record){
			self.accion_id.setValue(record.data.id);
			self.de_accion.setValue(record.data.de_accion);
			this.collapse();
		}
	    });

	    this.de_accion = new Ext.form.TextArea({
		fieldLabel: '1.3. DESCRIPCIÓN',
		name: 'descripcion',
		allowBlank: false,
		height: 100,
		maxLength: 1200
	    });            

            this.store_ejecutor = new Ext.data.JsonStore({
                url: 'formulacion/modulos/seguimiento_ac/funcion.php',
                root: 'data',
		baseParams: {
                    op: 3
                },
                fields: ['id_ejecutor', 'tx_ejecutor', 'inst_mision', 'inst_vision', 'inst_objetivos']
            });
            this.store_ejecutor.load({
                        callback: function(r, op, scs) {
                        self.id_ejecutor.setValue(r[0].data.id_ejecutor);
			self.inst_mision.setValue(r[0].data.inst_mision);
                        self.inst_vision.setValue(r[0].data.inst_vision);
                        self.inst_objetivos.setValue(r[0].data.inst_objetivos);
                        }
                    });                      

	    this.id_ejecutor = new Ext.form.ComboBox({
                    fieldLabel: '1.4. UNIDAD EJECUTORA RESPONSABLE',
                    store: this.store_ejecutor,
                    valueField: 'id_ejecutor',
                    displayField: 'tx_ejecutor',
                    hiddenName: 'id_ejecutores',
                    emptyText: 'Seleccione Unidad Ejecutora',
                    allowBlank: false,
                    forceSelection: true,
                    resizable: true,
                    triggerAction: 'all',
                    mode: 'local',
                       onSelect: function(record){
			self.id_ejecutor.setValue(record.data.id_ejecutor);
			self.inst_mision.setValue(record.data.inst_mision);
                        self.inst_vision.setValue(record.data.inst_vision);
                        self.inst_objetivos.setValue(record.data.inst_objetivos);
			this.collapse();
		}
	    });
                       
            
	    this.inst_mision = new Ext.form.TextArea({
                    fieldLabel: '1.4.1. MISION',
                    name: 'inst_mision',
                    allowBlank: false,
                    height: 60,
                    maxLength: 600
	    });
            
	    this.inst_vision = new Ext.form.TextArea({
                    fieldLabel: '1.4.2. VISION',
                    name: 'inst_vision',
                    allowBlank: false,
                    height: 60,
                    maxLength: 600
	    });
            
	    this.inst_objetivos = new Ext.form.TextArea({
                    fieldLabel: '1.4.3. OBJETIVOS DE LA INSTITUCION',
                    name: 'inst_objetivos',
                    allowBlank: false,
                    height: 100,
                    maxLength: 3000
	    });            

            this.store_situacion = new Ext.data.JsonStore({
                proxy: new Ext.data.HttpProxy({
                    url: 'formulacion/modulos/seguimiento_ac/funcion.php'
                }),
                baseParams: {
                    op: 4
                },
                root: 'data',
                fields: [
                    'co_situacion_presupuestaria',
                    'tx_situacion_presupuestaria'
                ]
            });

            this.store_sector = new Ext.data.JsonStore({
                proxy: new Ext.data.HttpProxy({
                    url: 'formulacion/modulos/seguimiento_ac/funcion.php'
                }),
                baseParams: {
                    op: 5
                },
                root: 'data',
                fields: ['co_sector', 'tx_descripcion']
            });

            this.store_subsector = new Ext.data.JsonStore({
                url: 'formulacion/modulos/seguimiento_ac/funcion.php',
                root: 'data',
                baseParams: {
                    op: 6
                },
                fields: ['co_sectores', 'co_sub_sector', 'tx_sub_sector']
            });

            this.fieldset1 = new Ext.form.FieldSet({
                defaults: {
                    width: ancho,
                },
                items: [{
                    xtype: 'hidden',
                    name: 'id'
                }, {
                    xtype: 'hidden',
                    name: 'id_ejercicio',
                }, {
                    xtype: 'textfield',
                    fieldLabel: '1.0. CÓDIGO DE LA ACCIÓN CENTRALIZADA',
                    name: 'codigo',
                    readOnly: true,
                    style: 'background:#c9c9c9;'
                }, {
                    xtype: 'textfield',
                    fieldLabel: '1.1. CÓDIGO DEL SISTEMA',
                    name: 'co_sistema',
                    readOnly: true,
                    style: 'background:#c9c9c9;'
                },this.accion_id
                /*{
                    xtype: 'combo',
                    store: this.store_accion,
                    fieldLabel: '1.2. TIPO DE ACCIÓN',
                    valueField: 'id',
                    displayField: 'de_nombre',
                    hiddenName: 'id_accion',
                    autoSelect: true,
                    forceSelection: true,
                    allowBlank: false,
                    emptyText: 'Seleccione el tipo de Acción Centralizada',
                    triggerAction: 'all',
                    mode: 'local'                   
                }, {
                    xtype: 'textarea',
                    fieldLabel: '1.3. DESCRIPCIÓN',
                    name: 'descripcion',
                    allowBlank: false,
                    height: 100,
                    maxLength: 200
                }*/,this.de_accion,
                    this.id_ejecutor,
                    this.inst_mision,
                    this.inst_vision,
                    this.inst_objetivos
                   /* {
                    xtype: 'combo',
                    fieldLabel: '1.4. UNIDAD EJECUTORA RESPONSABLE',
                    store: this.store_ejecutor,
                    valueField: 'id_ejecutor',
                    displayField: 'tx_ejecutor',
                    hiddenName: 'id_ejecutor',
                    emptyText: 'Seleccione Unidad Ejecutora',
                    allowBlank: false,
                    readOnly: ac.es_local,
                    style: ac.es_local ? 'background:#c9c9c9;' : '',
                    typeAhead: true,
                    forceSelection: true,
                    resizable: true,
                    triggerAction: 'all',
                    mode: 'local',
		    itemSelector: 'div.search-item',
		    tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_ejecutor}</div></div></tpl>'),
                }, {
                    xtype: 'textarea',
                    fieldLabel: '1.4.1. MISION',
                    name: 'inst_mision',
                    allowBlank: false,
                    height: 60,
                    maxLength: 600
                }, {
                    xtype: 'textarea',
                    fieldLabel: '1.4.2. VISION',
                    name: 'inst_vision',
                    allowBlank: false,
                    height: 60,
                    maxLength: 600
                }, {
                    xtype: 'textarea',
                    fieldLabel: '1.4.3. OBJETIVOS DE LA INSTITUCION',
                    name: 'inst_objetivos',
                    allowBlank: false,
                    height: 100,
                    maxLength: 3000
                }*/]
            });

            this.co_sector = new Ext.form.ComboBox({
                fieldLabel: '1.5.1. SECTOR',
                store: this.store_sector,
                typeAhead: true,
                valueField: 'co_sector',
                displayField: 'tx_descripcion',
                hiddenName: 'co_sector',
                forceSelection: true,
                resizable: true,
                triggerAction: 'all',
                emptyText: 'Seleccione Sector',
                mode: 'local',
                allowBlank: false,
                listeners: {
                    change: function() {
                        self.store_subsector.load({
                            params: {
                                co_sector: this.getValue()
                            }
                        });
                    },
                    beforeselect: function() {
                        self.co_sub_sector.clearValue();
                    }
                }
            });

            this.co_sub_sector = new Ext.form.ComboBox({
                fieldLabel: '1.5.2. SUB-SECTOR',
                store: this.store_subsector,
                typeAhead: true,
                valueField: 'co_sectores',
                displayField: 'tx_sub_sector',
                hiddenName: 'id_subsector',
                forceSelection: true,
                resizable: true,
                triggerAction: 'all',
                emptyText: 'Seleccione Sub Sector',
                selectOnFocus: true,
                mode: 'local',
                allowBlank: false
            });

            this.fieldset2 = new Ext.form.FieldSet({
                title: '1.5. CLASIFICACIÓN SECTORIAL',
                defaults: {
                    width: ancho,
                },
                items: [
                    this.co_sector,
                    this.co_sub_sector
                ]
            });

            this.validarFecha = function() {
                var sd = self.fecha_inicio.getValue();
                var ed = self.fecha_fin.getValue();
                if (sd <= ed) {
                    return true;
                }
                Ext.Msg.alert('Notificación',
                    'La Fecha de Inicio no debe <br>ser Mayor que la Fecha de Culminación');
                return false;
            };

            this.fecha_inicio = new Ext.form.DateField({
                fieldLabel: '1.6. FECHA DE INICIO',
                name: 'fecha_inicio',
                width: 100,
                allowBlank: false,
                format: 'd-m-Y',
                value: config.ac.fecha_inicio,
                minValue: config.ac.fecha_inicio,
                maxValue: config.ac.fecha_fin,
                validationEvent: 'change',
                validator: this.validarFecha
            });

            this.fecha_fin = new Ext.form.DateField({
                fieldLabel: '1.7. FECHA DE CULMINACIÓN',
                name: 'fecha_fin',
                width: 100,
                allowBlank: false,
                format: 'd-m-Y',
                value: config.ac.fecha_fin,
                minValue: config.ac.fecha_inicio,
                maxValue: config.ac.fecha_fin,
                validationEvent: 'change',
                validator: this.validarFecha
            });

            this.fieldset3 = new Ext.form.FieldSet({
                defaults: {
                    width: ancho,
                },
                items: [
                    this.fecha_inicio,
                    this.fecha_fin,
                    {
                        xtype: 'combo',
                        fieldLabel: '1.8. SITUACIÓN PRESUPUESTARIA',
                        store: this.store_situacion,
                        typeAhead: true,
                        valueField: 'co_situacion_presupuestaria',
                        displayField: 'tx_situacion_presupuestaria',
                        hiddenName: 'co_situacion_presupuestaria',
                        forceSelection: true,
                        resizable: true,
                        triggerAction: 'all',
                        emptyText: 'Seleccione Situacion Presupuestaria...',
                        selectOnFocus: true,
                        mode: 'local',
                        allowBlank: false
                    }, {
                        xtype: 'numberfield',
                        fieldLabel: '1.9. MONTO TOTAL (BS.)',
                        name: 'monto',
                        allowBlank: false,
                        allowDecimals: false,
                        minLength: 1,
                        maxLength: 12,
                        allowNegative: false,
                        emptyText: '0',
                    }, {
			xtype: 'numberfield',
			fieldLabel: '1.9.1. POBLACIÓN A BENEFICIAR',
			name: 'nu_po_beneficiar',
                        allowBlank: false,
                        allowDecimals: false,
                        minLength: 1,
                        maxLength: 12,
                        allowNegative: false,
                        emptyText: '0',
		    }, {
			xtype: 'numberfield',
			fieldLabel: '1.9.2. EMPLEOS PREVISTOS',
			name: 'nu_em_previsto',
                        allowBlank: false,
                        allowDecimals: false,
                        minLength: 1,
                        maxLength: 12,
                        allowNegative: false,
                        emptyText: '0',
		    }, {
			xtype: 'textarea',
			fieldLabel: '1.9.3. PRODUCTO PROGRAMADO DEL OBJETIVO',
			name: 'tx_pr_objetivo',
			allowBlank: false,
			height: 60,
			maxLength: 600
                    },{
			xtype: 'textarea',
			fieldLabel: '1.9.4. RESULTADOS ESPERADOS',
			name: 'tx_re_esperado',
			allowBlank: false,
			height: 60,
			maxLength: 600
                    }
                ]
            });

            config = Ext.apply({
                detalles: false,
                title: '1. DATOS BÁSICOS',
                deferredRender: false,
                autoWidth: true,
                autoHeight: true,
                padding: '10px',
                labelWidth: 200,
                labelSeparator: '',
                labelAlign: 'right',
                items: [
                    self.fieldset1,
                    self.fieldset2,
                    self.fieldset3
                ],
                bbar: [
                    '->', {
                        formBind: true,
                        text: 'Guardar',
                        iconCls: 'icon-guardar',
                        handler: function() {
                            var forma = self.getForm();
                            if (!forma.isValid()) {
                                Ext.Msg.alert("Alerta", "Debe ingresar los campos en rojo");
                                return false;
                            }
                            var enviarCambios = function() {
                                forma.submit({
                                    method: 'POST',
                                    url: 'formulacion/modulos/seguimiento_ac/funcion.php',
                                    params: {
                                        op: 99
                                    },
                                    waitMsg: 'Enviando datos, por favor espere..',
                                    waitTitle: 'Enviando',
                                    failure: function(form, action) {
                                        Ext.MessageBox.alert('Error en transacción', action.result.msg);
                                    },
                                    success: function(form, action) {
                                        if (action.result.success) {
                                            Ext.MessageBox.show({
                                                title: 'Mensaje',
                                                msg: action.result.msg,
                                                closable: false,
                                                icon: Ext.MessageBox.INFO,
                                                resizable: false,
                                                animEl: document.body,
                                                buttons: Ext.MessageBox.OK
                                            });
                                            if ( !self.ac.id ) {
                                                this.panelCambio = Ext.getCmp('tabpanel');
						this.panelCambio.remove('36');
                                            }
                                        } else {
                                            Ext.MessageBox.alert('Error en transacción',action.result.msg);
                                        }
                                    }
                                });
                            };
                            if (!!ac.id && (forma.getValues().id_accion !== ac.id_accion)) {
                                Ext.Msg.confirm('Atención',
                                    'Cambiar el Tipo de Acción de la AC, implica borrar'
                                    + ' (para mantener la consistencia), la información'
                                    + ' de las AE cargadas. ¿Desea continuar?',
                                    function(res) {
                                        if ( res === 'yes' ) {
                                            enviarCambios();
                                        }
                                    }
                                ).setIcon(Ext.MessageBox.WARNING);
                            } else {
                                enviarCambios();
                            }
                        }
                    }
                ]
            }, config);

            this.callParent(arguments);

            var intermedio = function(nombre) {
                return function(cb) {
                    self['store_' + nombre].load({
                        callback: function(r, op, scs) {
                            cb(scs ? null : nombre);
                        }
                    });
                };
            };

            this.on('beforerender', function() {
                async.parallel([
                        intermedio( 'accion'),
//                        intermedio( 'ejecutor'),
                        intermedio( 'situacion'),
                        function(cb) {
                            async.series([
                                    function(cb) {
                                        self.store_sector.load({
                                            callback: function(r, op, scs) {
                                                self.co_sector.setValue(
                                                    self.ac.co_sector
                                                );
                                                cb(scs ? null : 'sector');
                                            }
                                        });
                                    },
                                    function(cb) {
                                        if (self.ac.co_sector) {
                                            self.store_subsector.load({
                                                params: {
                                                    co_sector: self.ac.co_sector
                                                },
                                                callback: function(r, op, scs) {
                                                    self.co_sub_sector.setValue(
                                                        self.ac.id_subsector
                                                    );
                                                    cb(scs ? null : 'sub-sector');
                                                }
                                            });
                                        } else {
                                            cb(null);
                                        }
                                    },
                                ],
                                function(err) {
                                    cb(err);
                                });
                        },
                    ],
                    function(err) {
                        if (err) {
                            console.log(err);
                        } else {
                            self.getForm().setValues(self.ac);
                        }
                    });
            });
        }
    });

}(Ext, Reingsys, async, paqueteComunJS, opcionPlanificador));
