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
		border: false,
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
                url: 'auxiliar/accion/tipo',
                root: 'data',
                fields: [
                    'id', {
                        name: 'nombre',
                        convert: function(v, r) {
                            return r.nu_original + ' - ' + r.de_nombre;
                        }
                    },
		                'de_accion'
                ]
            });
           /* this.store_accion = new Ext.data.JsonStore({
                proxy: new Ext.data.HttpProxy({
                    /*url: 'formulacion/modulos/accionCentralizada/orm.php/tipo/accion',
                    url: 'auxiliar/accion/tipo',
                    /*method: 'POST'
                }),
                /*baseParams: {
                    op: 1
                },
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
            });*/

	    this.accion_id = new Ext.form.ComboBox({
		fieldLabel:'1.3. TIPO DE PROGRAMA',
		store: this.store_accion,
		typeAhead: true,
		valueField: 'id',
		displayField:'nombre',
		hiddenName:'id_accion',
		forceSelection:true,
		resizable:true,
		triggerAction: 'all',
		emptyText: 'Seleccione el tipo de Programa',
		selectOnFocus: true,
		mode: 'local',
		width:400,
		resizable:true,
		allowBlank:false,
		onSelect: function(record){
			self.accion_id.setValue(record.data.id);
			self.de_accion.setValue(record.data.de_accion);
			this.collapse();
		}
	    });

	    this.de_accion = new Ext.form.TextArea({
		fieldLabel: '1.4. DESCRIPCIÓN',
		name: 'descripcion',
		allowBlank: false,
		height: 100,
		maxLength: 1200
	    });

            this.store_ejecutor = new Ext.data.JsonStore({
                /*url: 'formulacion/modulos/usuario/funcion.php?op=5',*/
                url: 'auxiliar/ejecutor/activo',
                root: 'data',
                fields: ['id_ejecutor', 'tx_ejecutor',
                {
                    name: 'nombre',
                    convert: function(v, r) {
                        return r.id_ejecutor + ' - ' + r.tx_ejecutor;
                    }
                }]
            });
            
            
            this.store_sector = new Ext.data.JsonStore({
                proxy: new Ext.data.HttpProxy({
                    /*url: 'formulacion/modulos/proyecto/funcion.php',*/
                    url: 'auxiliar/poa/sector',
                    method: 'GET'
                }),
                /*baseParams: {
                    op: 3
                },*/
                root: 'data',
                fields: ['id','co_sector', 'nu_descripcion']
            });

            this.co_sector = new Ext.form.ComboBox({
                fieldLabel: '1.2. SECTOR',
                store: this.store_sector,
                typeAhead: true,
                valueField: 'id',
                displayField: 'nu_descripcion',
                hiddenName: 'id_co_sector',
                forceSelection: true,
                resizable: true,
                triggerAction: 'all',
                emptyText: 'Seleccione Sector',
                mode: 'local',
                width:400,
                allowBlank: false,
                listeners: {
                    change: function() {
                        self.store_accion.load({
                            params: {
                                co_sector: this.getValue()
                            }
                        });
                    },
                    beforeselect: function() {
                        self.accion_id.clearValue();
                    }
                }
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
                    fieldLabel: '1.0. CÓDIGO DEL PROGRAMA',
                    name: 'codigo',
                    readOnly: true,
                    style: 'background:#c9c9c9;'
                }, {
                    xtype: 'textfield',
                    fieldLabel: '1.1. CÓDIGO DEL SISTEMA',
                    name: 'co_sistema',
                    readOnly: true,
                    style: 'background:#c9c9c9;'
                },
                this.co_sector,
		this.accion_id
		/*,{
                    xtype: 'combo',
                    store: this.store_accion,
                    fieldLabel: '1.2. TIPO DE ACCIÓN',
                    valueField: 'id',
                    displayField: 'nombre',
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
		{
                    xtype: 'combo',
                    fieldLabel: '1.5. UNIDAD EJECUTORA RESPONSABLE',
                    store: this.store_ejecutor,
                    valueField: 'id_ejecutor',
                    displayField: 'nombre',
                    hiddenName: 'id_ejecutor',
                    emptyText: 'Seleccione Unidad Ejecutora',
                    allowBlank: false,
                    readOnly: ac.id?true:ac.es_local,
                    style: ac.es_local ? 'background:#c9c9c9;' : '',
                    typeAhead: true,
                    forceSelection: true,
                    resizable: true,
                    triggerAction: 'all',
                    mode: 'local'
                }, {
                    xtype: 'textarea',
                    fieldLabel: '1.5.1. MISION',
                    name: 'inst_mision',
                    allowBlank: false,
                    height: 60,
                    maxLength: 3000
                }, {
                    xtype: 'textarea',
                    fieldLabel: '1.5.2. VISION',
                    name: 'inst_vision',
                    allowBlank: false,
                    height: 60,
                    maxLength: 3000
                }, {
                    xtype: 'textarea',
                    fieldLabel: '1.5.3. OBJETIVOS DE LA INSTITUCION',
                    name: 'inst_objetivos',
                    allowBlank: false,
                    height: 100,
                    maxLength: 6000
                }]
            });



            this.store_subsector = new Ext.data.JsonStore({
                /*url: 'formulacion/modulos/proyecto/funcion.php?op=4',*/
                url: 'auxiliar/poa/subsector',
                root: 'data',
                fields: ['id', 'co_sub_sector', 'nu_descripcion']
            });

            this.co_sub_sector = new Ext.form.ComboBox({
                fieldLabel: '1.5.2. SUB-SECTOR',
                store: this.store_subsector,
                typeAhead: true,
                valueField: 'id',
                displayField: 'nu_descripcion',
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
//                    this.co_sector,
//                    this.co_sub_sector
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

            this.store_situacion = new Ext.data.JsonStore({
                proxy: new Ext.data.HttpProxy({
                    /*url: 'formulacion/modulos/proyecto/funcion.php',*/
                    url: 'auxiliar/poa/situacion',
                    method: 'GET'
                }),
                /*baseParams: {
                    op: 2
                },*/
                root: 'data',
                fields: [
                    'id',
                    'de_situacion_presupuestaria'
                ]
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
                        valueField: 'id',
                        displayField: 'de_situacion_presupuestaria',
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
                        maxLength: 20,
                        allowNegative: false,
                        emptyText: '0',
                    }, {
			xtype: 'numberfield',
			fieldLabel: '1.9.1. POBLACIÓN A BENEFICIAR',
			name: 'nu_po_beneficiar',
                        //allowBlank: false,
                        allowDecimals: false,
                        minLength: 0,
                        maxLength: 12,
                        allowNegative: false,
                        emptyText: '0',
		    }, {
			xtype: 'numberfield',
			fieldLabel: '1.9.2. EMPLEOS PREVISTOS',
			name: 'nu_em_previsto',
                        //allowBlank: false,
                        allowDecimals: false,
                        minLength: 0,
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
                    }, {
			xtype: 'textarea',
			fieldLabel: '1.9.4. RESULTADOS PROGRAMADOS',
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
//                    self.fieldset2,
                    self.fieldset3
                ],
                bbar: [
                    '->', {
                        formBind: true,
                        text: 'Guardar',
                        iconCls: 'icon-guardar',
			//hidden: ac.ac_guardar,
                        handler: function() {
                            var forma = self.getForm();
                            if (!forma.isValid()) {
                                Ext.Msg.alert("Alerta",
                                    "Debe ingresar los campos en rojo");
                                return false;
                            }
                            var enviarCambios = function() {
                                forma.submit({
                                    method: 'POST',
                                    /*url: 'formulacion/modulos/accionCentralizada/funcion.php',
                                    params: {
                                        op: 99
                                    },*/
                                    url: 'ac/guardar',
                                    waitMsg: 'Enviando datos, por favor espere..',
                                    waitTitle: 'Enviando',
                                    failure: function(form, action) {
                                        /*Ext.MessageBox.alert('Error en transacción',
                                            action.result.msg);*/

                                            var errores = '';
                                            for(datos in action.result.msg){
                                              errores += action.result.msg[datos] + '<br>';
                                            }
                                            Ext.MessageBox.alert('Error en transacción', errores);
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
                                            //FIXME no...
                                            opcionPlanificador.main.store_acciones.reload();

                                            if ( !self.ac.id ) {
                                                var nac = action.result.data;

                                                //abre en el lugar, pero faltaría
                                                //cambiar el id del tab
                                                /*
                                                self.ac = forma.getValues();
                                                //los generados
                                                Ext.apply( self.ac, action.result.data );
                                                forma.setValues( action.result.data );
                                                self.crearTabsAdicionales();
                                                */

                                                //cierra y reabre
                                                //FIXME
                                                window.addTab(
                                                    nac.codigo,
                                                    'Programa '+ nac.codigo,
                                                    'formulacion/modulos/accionCentralizada/accion.php',
                                                    'load',
                                                    'icon-buscar',
                                                    'id=' + nac.id
                                                  );
                                                window.tabpanel.remove(12);
                                            }
                                        } else {
                                            Ext.MessageBox.alert('Error en transacción',
                                                action.result.msg);
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

            if ( self.ac.bloqueado ) {
                Reingsys.util.deshabilitarForma(self);
            }

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
                self.crearTabsAdicionales();
                async.parallel([
//                        intermedio( 'accion'),
                        intermedio( 'ejecutor'),
                        intermedio( 'situacion'),
                        function(cb) {
                            async.series([
                                    function(cb) {
                                        self.store_sector.load({
                                            callback: function(r, op, scs) {
                                                console.log(self.ac.id_subsector);
                                                self.co_sector.setValue(
                                                    self.ac.id_subsector
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
        },
        crearTabsAdicionales: function() {
            var i, lista;
            if (!!this.ac.id && !this.detalles) {
                lista = [{
                    xtype: 'accion_centralizada_vinculos',
                    title: '2. VINCULACIÓN CON LOS PLANES'
                }, {
                    xtype: 'accion_centralizada_localizacion',
                    title: '3. LOCALIZACIÓN'
                }, {
                    xtype: 'accion_centralizada_responsables',
                    title: '4. RESPONSABLES'
                }, {
                    xtype: 'accion_especifica',
                    title: '5. PROYECTOS',
                }];
                for (i = 0; i < lista.length; i++) {
                    this.padre.insert(i + 1, Ext.create(
                        Ext.apply(lista[i], {
                            'ac': this.ac
                        })
                    ));
                }
                this.detalles = true;
            }
        }
    });

    Ext.apply(Ext.form.VTypes, {
        phoneMask: /[\d\-\+]/,
        phoneText: 'No es un número telefónico válido. Debe estar en formato +NN-NNNN-NNNNNNN, los símbolos "+" y "-", el código de área y de país son opcionales',
        phone: function(v) {
            //Números de Venezuela...
            return (/^((((\+)(\d{2})|(\d{2}))(\-)?)(\d{4}(\-)?)|(\d{4}(\-)?))?(\d{7})$/).test(v);
        },
        cedulaMask: /[\dVEve\-]/,
        cedulaText: 'No es un formato de cédula válido. Ejemplos: V123456, e-231654, V-123456',
        cedula: function(v) {
            return (/^[VvEe](\-)?(\d{4,8})$/).test(v);
        }
    });

    Ext.define('AccionCentralizada.Responsables', {
        extend: 'Ext.Panel',
        xtype: 'accion_centralizada_responsables',
        constructor: function(config) {
            var self = this;

            var comunes = [{
                xtype: 'textfield',
                name: 'nombres',
                fieldLabel: 'Nombre',
                minLength: 4,
                maxLength: 80,
                allowBlank: false
            }, {
                xtype: 'textfield',
                vtype: 'cedula',
                name: 'cedula',
                fieldLabel: 'Cédula',
                allowBlank: false
            }, {
                xtype: 'textfield',
                name: 'cargo',
                fieldLabel: 'Cargo',
                minLength: 4,
                maxLength: 50,
                allowBlank: false
            }, {
                xtype: 'textfield',
                vtype: 'email',
                name: 'correo',
                fieldLabel: 'Correo electrónico',
                maxLength: 50,
                allowBlank: false
            }, {
                xtype: 'textfield',
                vtype: 'phone',
                name: 'telefono',
                fieldLabel: 'Teléfono',
                allowBlank: false
            }, {
                xtype: 'textfield',
                name: 'unidad',
                fieldLabel: 'Unidad de Adscripción',
                minLength: 4,
                maxLength: 50,
                allowBlank: false
            }];

            var tipos = {
                realizador: {
                    nombre: 'Planificador',
                    campos: comunes
                },
                registrador: {
                    nombre: 'Administrador',
                    campos: comunes
                },
                autorizador: {
                    nombre: 'Titular',
                    campos: comunes
                }
            };

            var itemes = Object.keys(tipos).map(function(k) {
                var v = tipos[k];
                var fs = {
                    xtype: 'fieldset',
                    title: v.nombre,
                    defaults: {
                        width: 320
                    }
                };
                var items = v.campos.map(function(i) {
                    //en algún lado hay que clonarlo
                    var n = Ext.apply({}, i);
                    n.name = k + '_' + n.name;
                    return n;
                });

                fs.items = items;
                return fs;
            });

            this.actualizar = 'f';

            this.forma = Ext.create({
                xtype: 'form',
                bbar: [
                    '->', {
                        text: 'Guardar',
                        iconCls: 'icon-guardar',
                        handler: function(btn) {
                            var forma = self.forma.getForm();
                            if (!forma.isValid()) {
                                Ext.Msg.alert('Alerta',
                                    'Existen campos con valores inválidos');
                                return false;
                            }
                            forma.submit({
                                method: 'POST',
                                url: 'formulacion/modulos/accionCentralizada/funcion.php',
                                params: {
                                    op: 13,
                                    id_accion_centralizada: self.ac.id,
                                    up: self.actualizar
                                },
                                waitMsg: 'Enviando datos, por favor espere..',
                                waitTitle: 'Enviando',
                                failure: function(form, action) {
                                    Ext.MessageBox.alert('Error en transacción',
                                        action.result.msg);
                                },
                                success: function(form, action) {
                                    if (action.result.success) {
                                        Ext.MessageBox.show({
                                            icon: Ext.MessageBox.INFO,
                                            title: 'Mensaje',
                                            msg: action.result.msg,
                                            closable: true,
                                            buttons: Ext.MessageBox.OK
                                        });
                                        self.actualizar = 't';
                                    }
                                }
                            });
                        }
                    }
                ],
                padding: '10px 4px',
		border: false,
                autoWidth: true,
                labelWidth: 150,
                labelSeparator: '',
                labelAlign: 'right',
                labelStyle: 'font-weight:bold;',
                items: itemes
            });

            config = Ext.apply({
                title: 'Responsables',
                items: [
                    this.forma
                ]
            }, config);

            this.callParent(arguments);

            if ( self.ac.bloqueado ) {
                Reingsys.util.deshabilitarForma(self.forma);
            }

            Ext.Ajax.request({
                method: 'POST',
                url: 'formulacion/modulos/accionCentralizada/funcion.php',
                params: {
                    op: 12,
                    id: self.ac.id,
                },
                success: function(result) {
                    var obj = Ext.util.JSON.decode(result.responseText);
                    if (obj.success) {
                        if ( obj.data ) {
                            self.actualizar = 't';
                            self.forma.getForm().setValues(obj.data);
                        }
                    }
                },
                failure: function() {
                    Ext.Msg.alert('Ocurrió un error contactando al servidor');
                }
            });
        }
    });

    Ext.define('AccionCentralizada.Vinculos', {
        extend: 'Ext.Panel',
        xtype: 'accion_centralizada_vinculos',
        constructor: function(config) {
            var self = this;

            this.actualizar = 'f';

            this.st_co_nodo = Ext.create({
                xtype: 'jsonstore',
                url: 'auxiliar/plan/nudo',
                root: 'data',
                fields: [
                    'co_nodo', 'tx_descripcion',
                    {
                        name: 'de_nodo',
                        convert: function(v, r) {
                            return r.co_nodo + ' - ' + r.tx_descripcion;
                        }
                    }
                ]
            });

            this.co_co_nodo = Ext.create({
                xtype: 'superboxselect',
                fieldLabel: 'NUDOS CRÍTICOS',
                store: self.st_co_nodo,
                typeAhead: true,
                allowQueryAll : false,
                valueField: 'co_nodo',
                displayField: 'de_nodo',
                hiddenName: 'co_nodo[]',
                forceSelection: true,
                resizable: true,
                triggerAction: 'all',
                emptyText: 'Seleccione Nodo',
                selectOnFocus: true,
                mode: 'local',
                hideOnSelect: false,
//                readOnly: true,
                style: 'background-color:#c9c9c9;'
            });

            var combos_n = [{
                nombre: 'Objetivo Histórico',
                url: 'auxiliar/objetivo/historico',
                valor: 'co_objetivo_historico',
                mostrar: 'tx_descripcion'
            },{
                nombre: 'Objetivo Nacional',
                url: 'auxiliar/objetivo/nacional',
                valor: 'co_objetivo_nacional',
                mostrar:'tx_descripcion'
            },{
                nombre: 'Objetivo Estratégico',
                url: 'auxiliar/objetivo/estrategico',
                valor: 'co_objetivo_estrategico',
                mostrar: 'tx_descripcion'
            },{
                nombre: 'Objetivo General',
                url: 'auxiliar/objetivo/general',
                valor: 'co_objetivo_general',
                mostrar: 'tx_descripcion'
            }];

            var combos = [{
                nombre: 'Objetivo',
                url: 'auxiliar/plan/objetivo',
                valor: 'co_objetivo_zulia',
                mostrar: 'tx_descripcion'
            }, {
                nombre: 'Problema',
                url: 'auxiliar/plan/macroproblema',
                valor: 'co_macroproblema',
                mostrar: 'tx_descripcion'
            }];

            var combos_z = [{
                nombre: 'Área Estratégica',
                url: 'auxiliar/plan/area',
                valor: 'co_area_estrategica',
                mostrar: 'tx_descripcion'
            }, {
                nombre: 'Ámbito',
                url: 'auxiliar/plan/ambito',
                valor: 'co_ambito_zulia',
                mostrar: 'tx_descripcion',
            }, {
                nombre: 'LÍNEA MATRIZ',
                url: 'auxiliar/plan/nudo',
                valor: 'co_nodo',
                mostrar: 'tx_descripcion',
            }];

            var cbxs = [];
            var cbxs_z = [];
            var cbxs_n = [];

            var crearCreaCombosNac = function( combos_n ) {
                return function(e){
                    self['st_' + e.valor] = Ext.create({
                        xtype: 'jsonstore',
                        url: e.url,
                        root: 'data',
                        fields: [e.mostrar, e.valor]
                    });
                    var combo = Ext.create({
                        xtype: 'combo',
                        store: self['st_' + e.valor],
                        fieldLabel: e.nombre.toUpperCase(),
                        valueField: e.valor,
                        displayField: e.mostrar,
                        hiddenName: e.valor,
                        autoSelect: true,
                        forceSelection: true,
                        allowBlank: false,
                        emptyText: 'Seleccione ' + e.nombre,
                        triggerAction: 'all',
                        itemSelector: 'div.search-item',
                        tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{'+e.mostrar+'}</div></div></tpl>'),                        
                        mode: 'local'
                    });
                    self['co_'+ e.valor] = combo;
                    combos_n.push(combo);
                };
            };

            var crearCreaCombos = function( combos ) {
                return function(e){
                    self['st_' + e.valor] = Ext.create({
                        xtype: 'jsonstore',
                        url: e.url,
                        root: 'data',
                        fields: [e.mostrar, e.valor]
                    });
                    var combo = Ext.create({
                        xtype: 'combo',
                        store: self['st_' + e.valor],
                        fieldLabel: e.nombre.toUpperCase(),
                        valueField: e.valor,
                        displayField: e.mostrar,
                        hiddenName: e.valor,
                        autoSelect: true,
                        forceSelection: true,
                        //allowBlank: false,
                        emptyText: 'Seleccione ' + e.nombre,
                        triggerAction: 'all',
                        mode: 'local',
                        itemSelector: 'div.search-item',
                        tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{'+e.mostrar+'}</div></div></tpl>'),                          
//                        readOnly: true,
                        style: 'background-color:#c9c9c9;'
                    });
                    self['co_'+ e.valor] = combo;
                    combos.push(combo);
                };
            };

            //crea los combos y stores
            combos.forEach(crearCreaCombos(cbxs));
            combos_z.forEach(crearCreaCombosNac(cbxs_z));
            combos_n.forEach(crearCreaCombosNac(cbxs_n));

//            cbxs.push(this.co_co_nodo);

            var ajusta = function(cbx, dep) {
                self['st_' + dep].on('beforeload', function(st, op) {
                    var params = {};
                    if ( Ext.isArray(cbx) ) {
                        Ext.iterate(cbx, function(cb){
                            params[cb] = self['co_' + cb].getValue();
                        });
                    } else {
                        params[cbx] = self['co_' + cbx].getValue();
                    }
                    op.params = params;
                    return true;
                });
            };

            var borrar = function(e) {
                self['co_' + e].clearValue();
            };

            //cascada
            ajusta('co_objetivo_historico', 'co_objetivo_nacional');
            ajusta(['co_objetivo_historico', 'co_objetivo_nacional'], 'co_objetivo_estrategico');
            ajusta(['co_objetivo_historico', 'co_objetivo_nacional', 'co_objetivo_estrategico'], 'co_objetivo_general');

            self.co_co_objetivo_historico.on('change', function(){
                [
                    'co_objetivo_nacional', 'co_objetivo_estrategico', 'co_objetivo_general'
                ].forEach(borrar);
                self.st_co_objetivo_nacional.load();
            });
            self.co_co_objetivo_nacional.on('change', function(){
                [
                    'co_objetivo_estrategico', 'co_objetivo_general'
                ].forEach(borrar);
                self.st_co_objetivo_estrategico.load();
            });
            self.co_co_objetivo_estrategico.on('change', function(){
                [
                    'co_objetivo_general'
                ].forEach(borrar);
                self.st_co_objetivo_general.load();
            });

            ajusta('co_area_estrategica', 'co_ambito_zulia');
            ajusta('co_ambito_zulia', 'co_objetivo_zulia');
            ajusta('co_ambito_zulia', 'co_macroproblema');
            ajusta('co_ambito_zulia', 'co_nodo');

            self.co_co_area_estrategica.on('change', function(){
                [
                    'co_ambito_zulia', 'co_objetivo_zulia', 'co_macroproblema', 'co_nodo'
                ].forEach(borrar);
                self.st_co_ambito_zulia.load();
            });
            self.co_co_ambito_zulia.on('change', function(){
                [
                    'co_objetivo_zulia', 'co_macroproblema', 'co_nodo'
                ].forEach(borrar);
                self.st_co_objetivo_zulia.load();
                self.st_co_macroproblema.load();
                self.st_co_nodo.load();
            });
//            self.co_co_macroproblema.on('change', function(){
//                [
//                    'co_nodo'
//                ].forEach(borrar);
////                self.st_co_nodo.load();
//            });

            this.forma = Ext.create({
                xtype: 'form',
                bbar: [
                    '->', {
                        text: 'Guardar',
                        iconCls: 'icon-guardar',
                        handler: function(btn) {
                            var forma = self.forma.getForm();
                            if (!forma.isValid()) {
                                Ext.Msg.alert('Alerta',
                                    'Existen campos con valores inválidos');
                                return false;
                            }
                            forma.submit({
                                method: 'POST',
                                url: 'formulacion/modulos/accionCentralizada/funcion.php',
                                params: {
                                    op: 16,
                                    id_accion_centralizada: self.ac.id,
                                    up: self.actualizar
                                },
                                waitMsg: 'Enviando datos, por favor espere..',
                                waitTitle: 'Enviando',
                                failure: function(form, action) {
                                    Ext.MessageBox.alert('Error en transacción',
                                        action.result.msg);
                                },
                                success: function(form, action) {
                                    if (action.result.success) {
                                        Ext.MessageBox.show({
                                            icon: Ext.MessageBox.INFO,
                                            title: 'Mensaje',
                                            msg: action.result.msg,
                                            closable: true,
                                            buttons: Ext.MessageBox.OK
                                        });
                                        self.actualizar = 't';
                                    }
                                }
                            });
                        }
                    }
                ],
                padding: '10px 4px',
                autoWidth: true,
		border: false,
                defaults: {
                    xtype: 'fieldset',
                    defaults: {
                        width: 500
                    },
                    labelWidth: 150,
                    labelSeparator: '',
                    labelAlign: 'right',
                    labelStyle: 'font-weight:bold;',
                },
                items: [{
                    title: 'OBJETIVOS DEL PLAN DE LA PATRIA',
                    items: cbxs_n
                }, {
                    title: 'OBJETIVOS DEL PLAN DE DESARROLLO DEL ZULIA (LÍNEA MATRIZ 2022-2025)',
                    items: [
                        cbxs_z,
                        cbxs
                    ] 
                }]
            });

            config = Ext.apply({
                title: 'VINCULACIÖN CON LOS PLANES',
                items: [
                    this.forma
                ]
            }, config);

            this.callParent(arguments);

            if ( self.ac.bloqueado ) {
                Reingsys.util.deshabilitarForma(self.forma);
            }

            //restaura valores
            var intermedio = function(nombre) {
                return function(cb) {
                    self['st_' + nombre].load({
                        callback: function(r, op, scs) {
                            self['co_' + nombre].setValue(
                                self.vinculos[nombre]
                            );
                            cb(scs ? null : nombre);
                        }
                    });
                };
            };

            Ext.Ajax.request({
                method: 'POST',
                url: 'formulacion/modulos/accionCentralizada/funcion.php',
                params: {
                    op: 15,
                    id: self.ac.id,
                },
                success: function(result) {
                    var obj = Ext.util.JSON.decode(result.responseText);
                    if (obj.success && obj.data) {
                        self.actualizar = 't';
                        self.vinculos = obj.data;

                        async.auto({
                            oh: intermedio('co_objetivo_historico'),
                            on: [ 'oh', intermedio('co_objetivo_nacional') ],
                            oe: [ 'on', intermedio('co_objetivo_estrategico') ],
                            og: [ 'oe', intermedio('co_objetivo_general') ],
                            ae: intermedio('co_area_estrategica'),
                            az: [ 'ae', intermedio('co_ambito_zulia') ],
                            o: [ 'az', intermedio('co_objetivo_zulia') ],
                            m: [ 'az', intermedio('co_macroproblema') ],
                            n: [ 'm', intermedio('co_nodo') ]
                        }, function( err ) {
                            if (err) {
                                console.log(err);
                            }
                        });
                    } else {
                        self.st_co_objetivo_historico.load();
                        self.st_co_area_estrategica.load();
                    }
                },
                failure: function() {
                    Ext.Msg.alert('Ocurrió un error contactando al servidor');
                }
            });
        }
    });

    Ext.define('AccionCentralizada.Localizacion', {
        extend: 'Ext.Panel',
        xtype: 'accion_centralizada_localizacion',
        constructor: function(config) {
            var self = this;

            var combos = [{
                nombre: 'Municipios',
                valor: 'co_municipio',
                mostrar: 'tx_municipio',
                url:'formulacion/modulos/proyecto/funcion.php?op=12',
                stExtra: {
                    autoLoad: true,
                    baseParams: {
                        co_estado: 23
                    }
                }
            }, {
                nombre: 'Parroquias',
                valor: 'co_parroquia',
                mostrar: 'tx_parroquia',
                url: 'formulacion/modulos/proyecto/funcion.php?op=13'
            }];

            var items = [];

            //crea los combos y stores
            combos.forEach(function(e) {
                self['st_' + e.valor] = Ext.create(
                    Ext.apply({
                    xtype: 'jsonstore',
                    url: e.url,
                    root: 'data',
                    fields: [e.mostrar, e.valor],
                }, e.stExtra || {} ));
                var combo = Ext.create({
                    xtype: 'combo',
                    store: self['st_' + e.valor],
                    fieldLabel: e.nombre.toUpperCase(),
                    valueField: e.valor,
                    displayField: e.mostrar,
                    autoSelect: true,
                    forceSelection: true,
                    emptyText: 'Seleccione ' + e.nombre,
                    triggerAction: 'all',
                    mode: 'local'
                });
                self['co_'+ e.valor] = combo;
                items.push(combo);
            });

            //cascada
            self.co_co_municipio.on('change', function(){
                self.co_co_parroquia.clearValue();
                self.st_co_parroquia.load({
                    params: {
                        co_municipio: this.getValue()
                    }
                });
            });

            var getMunicipio = function() {
                var co, idx;
                co = self.co_co_municipio.getValue();
                idx = self.st_co_municipio.find( 'co_municipio', co );
                if (idx > -1 ) {
                    return self.st_co_municipio.getAt(idx).data;
                }
                return null;
            };
            var getParroquia = function() {
                var co, idx;
                co = self.co_co_parroquia.getValue();
                idx = self.st_co_parroquia.find( 'co_parroquia', co );
                if (idx > -1 ) {
                    return self.st_co_parroquia.getAt(idx).data;
                }
                return null;
            };

            var agregarMunicipio = function() {
                var mun, r,id;
                mun = getMunicipio();
                if ( mun ) {
                    id = mun.co_municipio;
                    if ( !self.st_grid.getById( id ) ) {
                        r = new self.st_grid.recordType(mun, id);
                        self.st_grid.add(r);
                    }
                }
            };

            var agregarParroquia = function() {
                var mun, par, r, id;
                mun = getMunicipio();
                par = getParroquia();
                if ( par ) {
                    id = mun.co_municipio + '-' + par.co_parroquia;
                    if ( !self.st_grid.getById( id ) ) {
                        r = new self.st_grid.recordType(
                            Ext.apply(par, mun), id);
                        self.st_grid.add(r);
                    }
                }
            };

            this.st_grid = Ext.create({
                xtype: 'jsonstore',
                autoDestroy: true,
                url: 'formulacion/modulos/accionCentralizada/funcion.php',
                baseParams: {
                    op: 17,
                    id: config.ac.id
                },
                autoLoad: true,
                root: 'data',
                fields: [
                    'co_municipio', 'co_parroquia', 'tx_municipio',
                    'tx_parroquia'
                ]
            });

            this.grid = Ext.create({
                xtype: 'grid',
                title: 'Localidades',
                autoHeight: true,
                store: this.st_grid,
                columns: [
                    {
                        header: 'Municipio',
                        dataIndex: 'tx_municipio',
                        renderer: Reingsys.util.textoLargo,
                        width: 320
                    },
                    {
                        header: 'Parroquia',
                        dataIndex: 'tx_parroquia',
                        renderer: Reingsys.util.textoLargo,
                        width: 320
                    }
                ],
                bbar: [{
                        text: 'Borrar Seleccionado',
                        iconCls: 'icon-eliminar',
                        handler: function() {
                            var sm = self.grid.getSelectionModel();
                            if (sm.hasSelection()) {
                                sm.getSelections().forEach(function(r) {
                                    self.st_grid.remove(r);
                                });
                            }
                        }
                    }, '->', {
                        text: 'Guardar',
                        iconCls: 'icon-guardar',
                        handler: function(btn) {
                            var localidades = [];
                            self.st_grid.each(function(r){
                                var loc = {
                                    co_municipio: r.data.co_municipio
                                };
                                if ( r.data.co_parroquia ) {
                                    loc.co_parroquia = r.data.co_parroquia;
                                }
                                localidades.push(loc);
                            });

                            Ext.Ajax.request({
                                method: 'POST',
                                url: 'formulacion/modulos/accionCentralizada/funcion.php',
                                params: {
                                    op: 18,
                                    id_accion_centralizada: self.ac.id,
                                    localidades: Ext.util.JSON.encode(localidades)
                                },
                                failure: function() {
                                    Ext.MessageBox.alert(
                                        'Error contactando al servidor'
                                    );
                                },
                                success: function(result) {
                                    var obj = Ext.util.JSON.decode(
                                        result.responseText
                                    );
                                    if (obj.success) {
                                        Ext.MessageBox.show({
                                            icon: Ext.MessageBox.INFO,
                                            title: 'Mensaje',
                                            msg: obj.msg,
                                            closable: true,
                                            buttons: Ext.MessageBox.OK
                                        });
                                    } else {
                                        Ext.MessageBox.alert(
                                            'Error en transacción',
                                            obj.msg
                                        );
                                    }
                                }
                            });
                        }
                    }
                ]
            });

            this.forma = Ext.create({
                xtype: 'form',
                //region: 'center',
                tbar: [/*{
                    xtype: 'button',
                    text: 'Agregar Municipio',
                    iconCls: 'icon-agregar',
                    handler: agregarMunicipio
                }, */{
                    xtype: 'button',
                    text: 'Agregar Parroquia',
                    iconCls: 'icon-agregar',
                    handler: agregarParroquia
                }],
                padding: '10px 4px',
		border: false,
                autoWidth: true,
                labelWidth: 150,
                labelSeparator: '',
                labelAlign: 'right',
                labelStyle: 'font-weight:bold;',
                defaults: {
                    width: 500
                },
                items: items
            });

            config = Ext.apply({
                title: 'LOCALIZACIÓN',
                items: [
                    this.forma,
                    this.grid
                ],
            }, config);

            this.callParent(arguments);

            if ( self.ac.bloqueado ) {
                Reingsys.util.deshabilitarForma(self);
            }
        }
    });

}(Ext, Reingsys, async, paqueteComunJS, opcionPlanificador));
