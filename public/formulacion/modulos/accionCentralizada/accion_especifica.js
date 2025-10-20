(function(Ext, Reingsys, async) {
    Ext.define('AccionCentralizada.Distribucion', {
        extend: 'Ext.Window',
        xtype: 'accion_centralizada_distribucion',
        constructor: function(config) {
            var self = this;

            var campos = ['1', '2', '3', '4', '5', '6',
                '7', '8', '9', '10', '11', '12', 'min', 'max'
            ].map(function(e) {
                return {
                    name: e,
                    type: 'int'
                };
            });

            this.store = Ext.create({
                xtype: 'jsonstore',
                proxy: new Ext.data.HttpProxy({
                    method: 'POST',
                    api: {
                        read: 'formulacion/modulos/accionCentralizada/funcion.php?op='
                            + config.leer,
                        update: 'formulacion/modulos/accionCentralizada/funcion.php?op='
                            + config.actualizar
                    }
                }),
                baseParams: {
                    id_accion_centralizada: config.ac.id
                },
                writer: new Ext.data.JsonWriter({
                    encode: true,
                    writeAllFields: false
                }),
                autoLoad: true,
                autoSave: false,
                autoDestroy: true,
                root: 'data',
                fields: [
                    'id'
                ].concat(campos, [
                    't1', 't2', 't3', 't4',
                    'tot', 'totc'
                ])
            });

            this.store.on('exception', function(s, t, a, o, r) {
                Ext.Msg.alert('Error almacenando los cambios', r.raw.msg)
                    .setIcon(Ext.MessageBox.ERROR);
                self.store.reload();
            });

            var editor = new Ext.ux.grid.RowEditor({
                saveText: 'Ok',
                cancelText: 'Cancelar'
            });

            editor.on('validateedit', function(re, chg, rec, idx) {
                var acum = 0,
                    i, k, keys;
                keys = Object.keys(chg);
                for (i = 0; i < keys.length; i++) {
                    k = keys[i];
                    if (k < rec.data.min || k > rec.data.max) {
                        Ext.Msg.alert( 'Atención',
                            'No pueden asignarse recursos a meses fuera'
                                + ' del rango de la Acción Específica'
                        ).setIcon(Ext.MessageBox.WARNING);
                        return false;
                    }
                    acum += chg[k] - rec.data[k];
                }
                if (acum !== 0) {
                    Ext.Msg.alert( 'Atención',
                            'La cantidad total por Acción Específica debe'
                            + ' coincidir con el declarado'
                    ).setIcon(Ext.MessageBox.ERROR);
                    return false;
                }
                return true;
            });

            var meses = [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
                'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ].map(function(e, i) {
                return {
                    header: e,
                    dataIndex: (i + 1).toString(),
                    editor: {
                        xtype: 'numberfield',
                        allowBlank: false,
                        allowDecimals: false,
                        allowNegative: false
                    }
                };
            });

            var trimestres = [1, 2, 3, 4].map(function(e) {
                return {
                    header: 'Trimestre ' + e,
                    dataIndex: 't' + e,
                    renderer: function(value, metaData, record) {
                        var i, t = e - 1, acum = 0;
                        for (i = (t * 3) + 1; i < (t * 3) + 3 + 1; i++) {
                            acum += record.data[i.toString()];
                        }
                        return config.formato(acum);
                    }
                };
            });

            var columnas = [];

            meses.forEach(function(e, i) {
                columnas.push(e);
                if ((i+1) % 3 === 0) {
                    columnas.push(trimestres.shift());
                }
            });

            this.grid = Ext.create({
                xtype: 'grid',
                store: self.store,
                plugins: [editor],
                flex: 1,
                stripeRows: true,
                viewConfig: {
                    autoFit: true
                },
                minColumnWidth: 70,
                bbar: [{
                    text: 'Editar',
                    iconCls: 'icon-editar',
                    handler: function() {
                        var r = self.grid.getSelectionModel().getSelected();
                        editor.startEditing(self.store.indexOf(r), false);
                    }
                }, '->', {
                    text: 'Guardar',
                    iconCls: 'icon-guardar',
                    handler: function() {
                        self.store.save();
                    }
                }],
                colModel: new Ext.grid.ColumnModel({
                    defaults: {
                        menuDisabled: false,
                        sortable: true,
                        editor: {
                            xtype: 'numberfield',
                            readOnly: true
                        },
                        renderer: config.formato
                    },
                    columns: [{
                        header: 'NÚMERO',
                        width: 60,
                        dataIndex: 'id',
                        renderer: null
                    }].concat(columnas, [{
                        header: 'TOTAL',
                        dataIndex: 'tot'
                    }])
                })
            });

            config = Ext.apply({
                title: 'Distribución ' + config.tipo
                    + ' de la A.C.: ' + config.ac.codigo,
                modal: true,
                maximizable: true,
                resizable: true,
                x: 0,
                y: 0,
                width: 500,
                height: 400,
                layout: 'fit',
                items: [
                    this.grid
                ]
            }, config);

            this.callParent(arguments);

            if ( self.ac.bloqueado ) {
                Reingsys.util.deshabilitarForma(self);
            }
        }
    });

    Ext.define('AccionCentralizada.AccionEspecificaForm', {
        extend: 'Ext.Window',
        xtype: 'accion_especifica_forma',
        constructor: function(config) {
            var self = this;

            this.actualizar = null;

            this.store_unidad = Ext.create({
                xtype: 'jsonstore',
                url: 'formulacion/modulos/usuario/funcion.php?op=5',
                root: 'data',
                fields: [
                    { name: 'id_ejecutor'},
                    { name: 'tx_ejecutor'},
                    { name: 'de_ejecutor',
                        convert: function(v, r) {
                            return r.id_ejecutor + ' - ' + r.tx_ejecutor;
                        }
					}
                ]
            });

            this.store_accion = new Ext.data.JsonStore({
                proxy: new Ext.data.HttpProxy({
                    url: 'auxiliar/ac/ae',
                    method: 'GET'
                }),
                baseParams: {
                    op: 14,
                    id_accion: config.ac.id_accion
                },
                root: 'data',
                fields: [
                    'id', 'numero', {
                        name: 'nombre',
                        convert: function(v, r) {
                            return r.numero + ' - ' + r.nombre;
                        }
                    }
                ]
            });

            this.store_fondo = new Ext.data.JsonStore({
                url: 'formulacion/modulos/accionDistribucion/funcion.php?op=4',
                root: 'data',
                fields: ['co_tipo_fondo', 'tx_tipo_fondo']
            });

            this.store_fondos = Ext.create({
                xtype: 'arraystore',
                autoDestroy: true,
                idIndex: 0,
                fields: [
                    'co_tipo_fondo', 'tx_tipo_fondo',
                    {name: 'monto', type: 'int'}
                ]
            });

            this.store_medida = new Ext.data.JsonStore({
                url: 'formulacion/modulos/proyecto/funcion.php?op=16',
                root: 'data',
                fields: [{
                    name: 'co_unidades_medida'
                }, {
                    name: 'tx_unidades_medida'
                }]
            });

            var validarFecha = function() {
                var sd = self.fecha_inicio.getValue();
                var ed = self.fecha_fin.getValue();
                if (sd <= ed) {
                    return true;
                }
                return 'La Fecha de Inicio no debe <br>ser Mayor que la Fecha de Culminación';
            };
            this.fecha_inicio = Ext.create({
                xtype: 'datefield',
                name: 'fecha_inicio',
                format: 'd-m-Y',
                minValue: config.ac.fecha_inicio,
                maxValue: config.ac.fecha_fin,
                value: config.ac.fecha_inicio,
                validator: validarFecha
            });
            this.fecha_fin = Ext.create({
                xtype: 'datefield',
                name: 'fecha_fin',
                format: 'd-m-Y',
                minValue: config.ac.fecha_inicio,
                maxValue: config.ac.fecha_fin,
                value: config.ac.fecha_fin,
                validator: validarFecha
            });

            this.forma = Ext.create({
                xtype: 'form',
                labelWidth: 150,
                labelAlign: 'right',
                labelStyle: 'font-weight:bold;',
                labelSeparator: '',
                padding: '10px 4px',
                defaults: {
                    width: 400
                },
                items: [{
                    xtype: 'hidden',
                    name: 'id_accion_centralizada',
                    value: config.ac.id
                }, {
                    xtype: 'combo',
                    store: this.store_accion,
                    fieldLabel: 'TIPO DE ACCIÓN',
                    valueField: 'id',
                    displayField: 'nombre',
                    hiddenName: 'id_accion',
                    autoSelect: true,
                    forceSelection: true,
                    allowBlank: false,
                    emptyText: 'Seleccione el tipo de Acción Específica',
                    triggerAction: 'all',
                    mode: 'local'
                },/* {
                    xtype: 'combo',
                    fieldLabel: 'UNIDAD EJECUTORA RESPONSABLE',
                    name: 'id_ejecutor_fld',
                    hiddenName: 'id_ejecutor',
                    allowBlank: false,
                    valueField: 'id_ejecutor',
                    //displayField: 'tx_ejecutor',
                    displayField: 'de_ejecutor',
                    resizable:true,
                    itemSelector: 'div.search-item',
                    tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{de_ejecutor}</div></div></tpl>'),
                    forceSelection: true,
                    typeAhead: true,
                    triggerAction: 'all',
                    emptyText: 'Seleccione Unidad Ejecutora',
                    mode: 'local',
                    store: self.store_unidad
                },*/ {
                    xtype: 'compositefield',
                    fieldLabel: 'FECHA DE INICIO',
                    items: [
                        self.fecha_inicio, {
                            xtype: 'displayfield',
                            value: '&nbsp;&nbsp;&nbsp; FECHA DE CULMINACIÓN:',
                        },
                        self.fecha_fin
                    ]
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'BIEN O SERVICIO',
                    minLength: 3,
                    maxLength: 128,
                    name: 'bien_servicio',
                    allowBlank: false
                }, {
                    xtype: 'textarea',
                    fieldLabel: 'OBJETIVO INSTITUCIONAL',
                    name: 'objetivo_institucional'
                }, {
                    xtype: 'hidden',
                    name: 'fondos'
                }, {
                    xtype: 'combo',
                    store: this.store_medida,
                    fieldLabel: 'UNIDAD DE MEDIDA',
                    valueField: 'co_unidades_medida',
                    displayField: 'tx_unidades_medida',
                    hiddenName: 'id_unidad_medida',
                    autoSelect: true,
                    forceSelection: true,
                    allowBlank: false,
                    emptyText: 'Seleccione la Unidad de Medida',
                    triggerAction: 'all',
                    mode: 'local'
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'META',
                    name: 'meta',
                    allowBlank: false,
                    allowNegative: false,
                    decimalPrecision: 0,
                    emptyText: '0',
                    maxLength: 8
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'MONTO',
                    name: 'monto',
                    allowBlank: false,
                    allowNegative: false,
                    allowDecimals: false,
                    emptyText: '0',
                    maxLength: 14
                }]
            });

            this.co_fondos = Ext.create({
                xtype: 'combo',
                store: this.store_fondo,
                valueField: 'co_tipo_fondo',
                displayField: 'tx_tipo_fondo',
                hiddenName: 'id_tipo_fondo',
                autoSelect: true,
                forceSelection: true,
                allowBlank: false,
                emptyText: 'Seleccione la Fuente de Financiamiento',
                triggerAction: 'all',
                mode: 'local'
            });

            this.fld_monto = Ext.create({
                xtype: 'numberfield',
                allowBlank: false,
                allowNegative: false,
                allowDecimals: false,
                minValue: 1,
                emptyText: '0',
                maxLength: 14
            });

            var getFuente = function() {
                var id, idx;
                id = self.co_fondos.getValue();
                idx = self.store_fondo.find( 'co_tipo_fondo', id );
                if (idx > -1 ) {
                    return self.store_fondo.getAt(idx).data;
                }
                return null;
            };

            var addFuente = function() {
                var fue, monto, r, id;
                fue = getFuente();
                if ( fue ) {
                    id = fue.co_tipo_fondo;
                    if ( !self.store_fondos.getById( id ) ) {
                        monto = self.fld_monto.getValue();
                        monto = monto === '' ? 0 : parseInt(monto, 10);
                        if ( monto === 0 ) {
                            return;
                        }
                        fue.monto = monto;
                        r = new self.store_fondos.recordType(fue, id);
                        self.store_fondos.add(r);
                    }
                }
            };

            this.grid_fondos = Ext.create({
                xtype: 'grid',
                title: 'Fuentes de Financiamiento',
                store: self.store_fondos,
                autoExpandColumn: 'fondo',
                colModel: new Ext.grid.ColumnModel({
                    defaults: {
                        menuDisabled: true,
                        sortable: false
                    },
                    columns: [{
                        id: 'fondo',
                        header: 'FUENTE FINANCIAMIENTO',
                        width: 80,
                        dataIndex: 'tx_tipo_fondo'
                    }, {
                        header: 'MONTO (BS)',
                        width: 130,
                        dataIndex: 'monto',
                        renderer: Reingsys.util.formatoNumero
                    }]
                }),
                stripeRows: true,
                tbar: [
                    this.co_fondos, this.fld_monto, {
                    xtype: 'button',
                    text: 'Agregar',
                    iconCls: 'icon-agregar',
                    handler: addFuente
                },{
                    xtype: 'button',
                    text: 'Eliminar',
                    iconCls: 'icon-eliminar',
                    handler: function() {
                        var sm = self.grid_fondos.getSelectionModel();
                        if (sm.hasSelection()) {
                            sm.getSelections().forEach(function(r) {
                                self.store_fondos.remove(r);
                            });
                        }
                    }
                }]
            });

            this.grid_fondos.flex = 1;

            config = Ext.apply({
                acid: null,
                ae: null,
                title: 'Añadir Acción Específica',
                modal: true,
                width: 600,
                height: 500,
                layout: 'vbox',
                layoutConfig: {
                    align: 'stretch'
                },
                items: [
                    this.forma,
                    this.grid_fondos
                ],
                bbar: [ '->', {
                    text: 'Guardar',
                    iconCls: 'icon-guardar',
                    handler: function(btn) {
                        var forma = self.forma.getForm(),
                            suma = 0,
                            fuentes = [];

                        if (!forma.isValid()) {
                            Ext.Msg.alert('Alerta', 'Existen campos inválidos');
                            return false;
                        }

                        self.store_fondos.each(function(r){
                            var enviar = {};
                            enviar.co_tipo_fondo = r.data.co_tipo_fondo;
                            enviar.monto = r.data.monto;
                            suma += r.data.monto;
                            fuentes.push(enviar);
                        });

                        if (parseInt(forma.getValues().monto, 10) !== suma) {
                            Ext.Msg.alert('Alerta', 'La suma de los montos de las Fuentes de Financiamiento no coincide con el monto declarado para la AE');
                            return false;
                        }

                        forma.setValues({
                            fondos: Ext.util.JSON.encode(fuentes)
                        });

                        forma.submit({
                            method: 'POST',
                            url: 'formulacion/modulos/accionCentralizada/funcion.php',
                            params: {
                                op: 4,
                                up: self.actualizar
                            },
                            waitMsg: 'Enviando datos, por favor espere...',
                            waitTitle: 'Enviando',
                            failure: function(form, action) {
                                Ext.MessageBox.alert('Error en transacción',
                                    action.result.msg);
                            },
                            success: function(form, action) {
                                if (action.result.success) {
                                    if ( self.actualizar ) {
                                        self.actualizar =
                                            forma.getValues().id_accion;
                                    }
                                    Ext.MessageBox.show({
                                        title: 'Mensaje',
                                        msg: action.result.msg,
                                        closable: false,
                                        icon: Ext.MessageBox.INFO,
                                        resizable: false,
                                        animEl: document.body,
                                        buttons: Ext.MessageBox.OK,
                                        fn: function() {
                                            self.close();
                                        }
                                    });
                                }
                            }
                        });
                    }
                }]
            }, config);

            this.callParent(arguments);

            if (self.ae) {
                self.actualizar = self.ae.id_accion;
                self.setTitle('Editar Acción Específica');
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
                if (self.ae) {
                    Ext.Ajax.request({
                        method: 'POST',
                        url: 'formulacion/modulos/accionCentralizada/funcion.php',
                        params: {
                            op: 21,
                            id_ac: self.ac.id,
                            id_ae: self.ae.id_accion
                        },
                        success: function(result) {
                            var resp = Ext.util.JSON.decode(result.responseText);
                            if ( resp.success ) {
                                self.store_fondos.loadData(resp.data);
                            } else {
                                Ext.Msg.alert('Ocurrió un error obteniendo las fuentes de financiamiento');
                            }
                        },
                        failure: function() {
                            Ext.Msg.alert('Ocurrió un error obteniendo las fuentes de financiamiento');
                        }
                    });
                }
                async.parallel(
                    ['unidad', 'medida', 'accion', 'fondo'].map(function(e) {
                        return intermedio(e);
                    }), function(err) {
                        if (err) {
                            console.log(err);
                        } else {
                            if (self.ae) {
                                self.forma.getForm().setValues(self.ae);
                            }
                        }
                    }
                );
            });
        }
    });

    Ext.define('AccionCentralizada.CargarPartidas', {
        extend: 'Ext.Window',
        xtype: 'accion_centralizada_partidas',
        constructor: function(config) {
            var self = this;
            config = Ext.apply({
                title: 'Cargar Partidas de Acción Centralizada',
                modal: true,
                width: 400,
                items: [{
                    xtype: 'form',
                    fileUpload: true,
                    labelAlign: 'right',
                    items: [{
                            xtype: 'hidden',
                            name: 'accion_centralizada',
                            value: config.ac.id
                        }, {
                            xtype: 'displayfield',
                            fieldLabel: 'Código',
                            value: config.ac.codigo
                        }, {
                            xtype: 'displayfield',
                            fieldLabel: 'Nombre',
                            value: config.ac.nombre
                        },
                        new Ext.ux.form.FileUploadField({
                            emptyText: 'Seleccione un Archivo',
                            fieldLabel: 'Archivo',
                            name: 'archivo',
                            buttonText: '',
                            width: 200,
                            buttonCfg: {
                                iconCls: 'icon-excel'
                            },
                            allowBlank: false
                        }), {
                            xtype: 'displayfield',
                            fieldLabel: '',
                            labelSeparator: '',
                            padding: '',
                            value: '* Recuerde verificar que el archivo contenga '
                                + config.cuenta + ' columnas contigüas de datos, '
                                + 'correspondientes a las acciones específicas'
                        }
                    ],
                    buttonAlign: 'right',
                    bbar: [{
                        text: 'Procesar',
                        iconCls: 'icon-guardar',
                        handler: function(btn) {
                            var forma = btn.findParentByType('form').getForm();
                            if (!forma.isValid()) {
                                Ext.Msg.alert('Alerta',
                                    'Debe seleccionar un Archivo');
                                return false;
                            }
                            forma.submit({
                                method: 'POST',
                                /*url: 'formulacion/modulos/accionCentralizada/funcion.php',*/
                                url: 'ac/ae/partida/masivo',
                                params: {
                                    op: 6,
                                    up: self.actualizar
                                },
                                waitMsg: 'Enviando datos, por favor espere..',
                                waitTitle: 'Enviando',
                                failure: function(form, action) {
                                  var errores = '';
                                  for(datos in action.result.msg){
                                    errores += action.result.msg[datos] + '<br>';
                                  }
                                    /*Ext.MessageBox.alert('Error en transacción',
                                        action.result.msg);*/
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
                                    }
                                }
                            });
                        }
                    }]
                }]
            }, config);

            this.callParent(arguments);
        }
    });

    Ext.define('AccionCentralizada.CargarPartidas.App', {
        extend: 'Ext.Window',
        xtype: 'ac_ae_partidas_app',
        constructor: function(config) {
            var self = this;
            config = Ext.apply({
                title: 'Cargar Partidas de Acción Especifica',
                modal: true,
                width: 400,
                items: [{
                    xtype: 'form',
                    fileUpload: true,
                    labelAlign: 'right',
                    border:false,
                    items: [{
                            xtype: 'hidden',
                            name: 'accion_centralizada',
                            value: config.ac.id
                        }, {
                            xtype: 'displayfield',
                            fieldLabel: 'Código AC',
                            value: config.ac.codigo
                        }, {
                            xtype: 'displayfield',
                            fieldLabel: 'Código AE',
                            value: config.ae.numero
                        },
                        new Ext.ux.form.FileUploadField({
                            emptyText: 'Seleccione un Archivo',
                            fieldLabel: 'Archivo',
                            name: 'archivo',
                            buttonText: '',
                            width: 200,
                            buttonCfg: {
                                iconCls: 'icon-excel'
                            },
                            allowBlank: false
                        }), {
                            xtype: 'displayfield',
                            fieldLabel: '',
                            labelSeparator: '',
                            padding: '',
                            value: '* Recuerde verificar que el archivo contenga '
                                + config.cuenta + ' columnas contigüas de datos, '
                                + 'correspondientes a las acciones específicas'
                        }
                    ],
                    buttonAlign: 'right',
                    bbar: [{
                        text: 'Procesar',
                        iconCls: 'icon-guardar',
                        handler: function(btn) {
                            var forma = btn.findParentByType('form').getForm();
                            if (!forma.isValid()) {
                                Ext.Msg.alert('Alerta',
                                    'Debe seleccionar un Archivo');
                                return false;
                            }
                            forma.submit({
                                method: 'POST',
                                url: 'ac/ae/partida/cargar',
                                params: {
                                    ac: config.ac.id,
                                    ae: config.ae.id_accion,
                                    up: self.actualizar
                                },
                                waitMsg: 'Enviando datos, por favor espere..',
                                waitTitle: 'Enviando',
                                failure: function(form, action) {
                                  var errores = '';
                                  for(datos in action.result.msg){
                                    errores += action.result.msg[datos] + '<br>';
                                  }
                                    /*Ext.MessageBox.alert('Error en transacción',
                                        action.result.msg);*/
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
                                    }
                                }
                            });
                        }
                    }]
                }]
            }, config);

            this.callParent(arguments);
        }
    });

    Ext.define('AccionCentralizada.AccionEspecifica_Partidas', {
        extend: 'Ext.Window',
        xtype: 'accion_especifica_partidas',
        constructor: function(config) {
            var self = this,
            tpagina = 30;

            this.store = new Ext.data.JsonStore({
                /*url: 'formulacion/modulos/accionCentralizada/funcion.php',*/
                url: 'ac/ae/partida/storeLista',
                baseParams: {
                    op: 10,
                    id_accion_centralizada: config.ac.id,
                    id_accion_especifica: config.ae.id_accion,
                    start: 0,
                    limit: tpagina
                },
                root: 'data',
                fields: [{
                    name: 'co_partida'
                }, {
                    name: 'tx_nombre'
                }, {
                    name: 'monto'
                }],
                idProperty: 'co_partida',
                autoLoad: true
            });

            this.grid = Ext.create({
                xtype: 'grid',
                store: self.store,
                border: false,
                colModel: new Ext.grid.ColumnModel({
                    defaults: {
                        menuDisabled: true,
                        sortable: false
                    },
                    columns: [{
                        id: 'partida',
                        header: 'PARTIDA',
                        width: 100,
                        dataIndex: 'co_partida'
                    }, {
                        header: 'DENOMINACIÓN',
                        width: 330,
                        dataIndex: 'tx_nombre'
                    }, {
                        header: 'MONTO (BS)',
                        width: 130,
                        dataIndex: 'monto',
                        renderer: Reingsys.util.formatoNumero
                    }]
                }),
                stripeRows: true,
                bbar: new Ext.PagingToolbar({
                    pageSize: tpagina,
                    store: self.store,
                    displayInfo: true,
                    displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
                    emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
                }),
                tbar: [{
                    xtype: 'button',
                    text: 'Descargar',
                    iconCls: 'icon-excel',
                    handler: function(){
                      bajar.load({
                          url: 'ac/ae/partida/'+config.ac.id+'/'+config.ae.id_accion+'/bajar'
                      });
                    }
                },'-',{
                    xtype: 'button',
                    text: 'Subir Partidas',
                    iconCls: 'icon-generar',
                    handler: function() {
                        var v = Ext.create({
                            xtype: 'ac_ae_partidas_app',
                            ac: self.ac,
                            ae: self.ae,
                            cuenta: self.store.getCount()
                        });
                        v.show();
                        v.on('close', function() {
                            self.store.reload();
                        });
                    }
                },'-',{
                    xtype: 'button',
                    text: 'Ver Desagregado',
                    iconCls: 'icon-reporteest',
                    handler: function() {
                      var v = Ext.create({
                          xtype: 'ac_ae_partidas_desagregado',
                          ac: self.ac,
                          ae: self.ae,
                          cuenta: self.store.getCount()
                      });
                      v.show();
                      v.on('close', function() {
                          self.store.reload();
                      });
                    }
                }]
            });

            config = Ext.apply({
                title: 'Partidas de la Acción Específica: ' +
                config.ac.codigo + ' - ' + config.ae.numero,
                modal: true,
                maximizable: true,
                resizable: true,
                x: 0,
                y: 0,
                width: 600,
                height: 400,
                layout: 'fit',
                items: [
                    this.grid
                ]
            }, config);

            this.callParent(arguments);
        }
    });

    Ext.define('AccionCentralizada.AccionEspecifica_Desagregado', {
        extend: 'Ext.Window',
        xtype: 'ac_ae_partidas_desagregado',
        constructor: function(config) {
            var self = this,
            tpagina = 30;

            this.store = new Ext.data.JsonStore({
                url: 'ac/ae/partida/desagregado/storeLista',
                baseParams: {
                    op: 10,
                    ac: config.ac.id,
                    ae: config.ae.id_accion,
                    start: 0,
                    limit: tpagina
                },
                root: 'data',
                fields: [{
                    name: 'co_partida'
                }, {
                    name: 'de_denominacion'
                }, {
                    name: 'nu_aplicacion'
                }, {
                    name: 'mo_partida'
                }],
                idProperty: 'co_partida',
                autoLoad: true
            });

            this.grid = Ext.create({
                xtype: 'grid',
                store: self.store,
                border: false,
                colModel: new Ext.grid.ColumnModel({
                    defaults: {
                        menuDisabled: true,
                        sortable: false
                    },
                    columns: [{
                        id: 'partida',
                        header: 'PARTIDA',
                        width: 100,
                        dataIndex: 'co_partida'
                    }, {
                        id: 'aplicacion',
                        header: 'APLICACION',
                        width: 100,
                        dataIndex: 'nu_aplicacion'
                    }, {
                        header: 'DENOMINACIÓN',
                        width: 330,
                        dataIndex: 'de_denominacion'
                    }, {
                        header: 'MONTO (BS)',
                        width: 130,
                        dataIndex: 'mo_partida',
                        renderer: Reingsys.util.formatoNumero
                    }]
                }),
                stripeRows: true,
                bbar: new Ext.PagingToolbar({
                    pageSize: tpagina,
                    store: self.store,
                    displayInfo: true,
                    displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
                    emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
                })
            });

            config = Ext.apply({
                title: 'Partidas Desagregadas de la Acción Específica: ' +
                config.ac.codigo + ' - ' + config.ae.numero,
                modal: true,
                maximizable: true,
                resizable: true,
                x: 30,
                y: 30,
                width: 700,
                height: 400,
                layout: 'fit',
                items: [
                    this.grid
                ]
            }, config);

            this.callParent(arguments);
        }
    });

    Ext.define('AccionCentralizada.AccionEspecifica', {
        extend: 'Ext.Panel',
        xtype: 'accion_especifica',
        constructor: function(config) {
            var self = this,
        tpagina = 20;

    this.store = new Ext.data.JsonStore({
        /*url: 'formulacion/modulos/accionCentralizada/funcion.php',*/
        url: 'ac/ae/storeLista',
        baseParams: {
            op: 3,
        id: config.ac.id,
        start: 0,
        limit: tpagina
        },
        autoSave: false,
        root: 'data',
        idProperty: 'id_accion',
        fields: [
        'id_accion', 'numero', 'nombre',
        'bien_servicio', 'monto', 'monto_calc', 'fecha_inicio',
        'fecha_fin', 'id_ejecutor', 'tx_ejecutor',
        'id_unidad_medida', 'tx_unidades_medida',
        'meta', 'npartidas', 'objetivo_institucional'
        ]
    });

    this.cargarAccion = new Ext.Button({
        text: 'Agregar',
        iconCls: 'icon-agregar',
        handler: function() {
            var v = Ext.create({
                xtype: 'accion_especifica_forma',
            ac: config.ac
            });
            v.show();
            v.on('close', function() {
                self.store.reload();
            });
        }
    });

    this.editarAccion = new Ext.Button({
        text: 'Editar',
        iconCls: 'icon-editar',
        disabled: true,
        handler: function() {
            var r = self.grid.getSelectionModel().getSelected();
            var v = Ext.create({
                xtype: 'accion_especifica_forma',
                ac: config.ac,
                ae: r.data
            });
            v.show();
            v.on('close', function() {
                self.store.reload();
            });
        }
    });

    this.verPartidas = new Ext.Button({
        text: 'Ver Partidas',
        iconCls: 'icon-reporteest',
        disabled: true,
        etiquetas: {
            ver: true
        },
        handler: function(btn) {
            var r = self.grid.getSelectionModel().getSelected();
            var v = Ext.create({
                xtype: 'accion_especifica_partidas',
                        ac: config.ac,
                        ae: r.data
                    });
                    v.show();
                    v.on('close', function() {
                        self.store.reload();
                    });
                }
            });

            this.verDistribucion = new Ext.Button({
                text: 'Distribución Financiera',
                iconCls: 'icon-reporteest',
                disabled: true,
                etiquetas: {
                    ver: true
                },
                handler: function(btn) {
                    var v = Ext.create({
                        xtype: 'accion_centralizada_distribucion',
                        ac: self.ac,
                        leer: 8,
                        actualizar: 19,
                        tipo: 'Financiera',
                        formato: Reingsys.util.formatoNumero
                    });
                    v.show();
                }
            });

            this.verDistribucionFisica = new Ext.Button({
                text: 'Distribución Física',
                iconCls: 'icon-reporteest',
                disabled: true,
                etiquetas: {
                    ver: true
                },
                handler: function(btn) {
                    var v = Ext.create({
                        xtype: 'accion_centralizada_distribucion',
                        ac: self.ac,
                        leer: 9,
                        actualizar: 20,
                        tipo: 'Física',
                        formato: function(v) { return v; }
                    });
                    v.show();
                }
            });

            //Eliminar un registro
            this.eliminar = new Ext.Button({
                text: 'Eliminar',
                iconCls: 'icon-eliminar',
                disabled: true,
                handler: function() {
                    var r = self.grid.getSelectionModel().getSelected();
                    Ext.MessageBox.confirm('Confirmación',
                        '¿Realmente desea eliminar la acción "' +
                        r.get('nombre') + '"?',
                        function(boton) {
                            if (boton === 'yes') {
                                Ext.Ajax.request({
                                    method: 'POST',
                                    url: 'formulacion/modulos/accionCentralizada/funcion.php',
                                    params: {
                                        op: 7,
                                        id_accion_centralizada: self.ac.id,
                                        id_accion_especifica: r.get('id_accion')
                                    },
                                    success: function(result) {
                                        var obj = Ext.util.JSON.decode(result.responseText);
                                        if (obj.success) {
                                            self.store.reload();
                                        }
                                        Ext.Msg.alert("Notificación", obj.msg);
                                    },
                                    failure: function() {
                                        Ext.Msg.alert("Ocurrió un error contactando al servidor");
                                    }
                                });
                            }
                        }
                    );
                }
            });

            this.cerrar = Ext.create({
                xtype: 'button',
                text: '"Cerrar si Cuadra"',
                iconCls: 'icon-guardar',
                handler: function(btn) {
                    var mb = Ext.Msg.wait('Esperando respuesta...');
                    Ext.Ajax.request({
                        method: 'POST',
                        //url: 'formulacion/modulos/accionCentralizada/funcion.php',
                        //url: 'formulacion/modulos/accionCentralizada/orm.php/cerrar/ac',
                        url: 'ac/cerrar',
                        params: {
                            op: 11,
                            id_accion_centralizada: self.ac.id
                        },
                        failure: function() {
                            mb.hide();
                            Ext.Msg.alert('Error',
                                'Ocurrió un error en la comunicación con el servidor');
                        },
                        success: function(resp) {
                            var r = false;
                            mb.hide();
                            try {
                                r = Ext.util.JSON.decode(resp.responseText);
                                if (r.success) {
                                    Ext.Msg.show({
                                        title: 'Mensaje',
                                        msg: 'La Acción Centralizada se ha cerrado',
                                        closable: false,
                                        icon: Ext.Msg.INFO,
                                        buttons: Ext.Msg.OK,
                                        fn: function() {
                                            //TODO cerrar?
                                            console.log('AC cerrada');
                                            opcionPlanificador.main.store_acciones.reload();
                                            this.panelCambio = Ext.getCmp('tabpanel');
                                            this.panelCambio.remove(self.ac.codigo);
                                        }
                                    });
                                } else {
                          					var errores = '';
                          					for(datos in r.msg){
                          						errores += r.msg[datos] + '<br>';
                          					}
                                    Ext.Msg.alert('Error', errores);
                                }
                            } catch (e) {
                                Ext.Msg.alert('Error',
                                    'Respuesta del servidor inválida');
                            }
                        }
                    });
                }
            });

            this.cargarPartida = new Ext.Button({
                text: 'Cargar Partidas',
                iconCls: 'icon-excel',
                disabled: true,
                handler: function() {
                    var v = Ext.create({
                        xtype: 'accion_centralizada_partidas',
                        ac: self.ac,
                        cuenta: self.store.getCount()
                    });
                    v.show();
                    v.on('close', function() {
                        self.store.reload();
                    });
                }
            });

	this.descargarFormato = new Ext.Button({
	    text: 'Descargar Formato',
	    iconCls: 'icon-descargar',
	    handler: function(){
		bajar.load({
		    url: 'formulacion/modulos/descargas/FORMATO_AE_PARTIDAS_'+config.ac.id_ejercicio+'.xlsx'
		});
	    }
	});

            this.rowSelModel = new Ext.grid.RowSelectionModel();

            this.grid = Ext.create({
                xtype: 'grid',
                store: this.store,
                loadMask: true,
                autoHeight: true,
                stripeRows: true,
                autoScroll: true,
		border: false,
                tbar: [
                    this.cargarAccion,
                    this.editarAccion,
                    this.cargarPartida,
		    this.descargarFormato,
                    this.verPartidas,
                    this.verDistribucion,
                    this.verDistribucionFisica,
                    this.eliminar,
                    '->',
                    this.cerrar
                ],
                columns: [{
                    header: 'NÚMERO',
                    width: 60,
                    menuDisabled: true,
                    dataIndex: 'numero'
                }, {
                    header: 'NOMBRE',
                    width: 200,
                    menuDisabled: true,
                    sortable: true,
                    renderer: Reingsys.util.textoLargo,
                    dataIndex: 'nombre'
                }, {
                    header: 'MONTO Bs.',
                    width: 120,
                    menuDisabled: true,
                    sortable: true,
                    renderer: Reingsys.util.formatoNumero,
                    dataIndex: 'monto'
                }, {
                    header: 'MONTO REGISTRADO Bs.',
                    width: 120,
                    menuDisabled: true,
                    sortable: true,
                    renderer: Reingsys.util.formatoNumero,
                    dataIndex: 'monto_calc'
                }, {
                    header: 'UNIDAD EJECUTORA RESPONSABLE',
                    width: 200,
                    menuDisabled: true,
                    sortable: true,
                    renderer: Reingsys.util.textoLargo,
                    dataIndex: 'tx_ejecutor'
                }, {
                    header: 'META',
                    width: 120,
                    menuDisabled: true,
                    sortable: true,
                    renderer: function(v, m, r) {
                        return v + ' ' + r.get('tx_unidades_medida');
                    },
                    dataIndex: 'meta'
                }, {
                    header: 'FECHA DE INICIO',
                    width: 120,
                    format: 'd-m-Y',
                    menuDisabled: true,
                    sortable: true,
                    dataIndex: 'fecha_inicio'
                }, {
                    header: 'FECHA DE CULMINACIÓN',
                    width: 150,
                    format: 'd-m-Y',
                    menuDisabled: true,
                    sortable: true,
                    dataIndex: 'fecha_fin'
                }],
                bbar: new Ext.PagingToolbar({
                    pageSize: tpagina,
                    store: this.store,
                    displayInfo: true,
                    displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
                    emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
                }),
                selModel: this.rowSelModel
            });

            config = Ext.apply({
                ac: null,
                datos: null,
                autoWidth: true,
                layout: 'fit',
                items: [
                    this.grid
                ]
            }, config);

            this.callParent(arguments);

            if ( self.ac.bloqueado ) {
                Reingsys.util.deshabilitarForma(self);
                self.verDistribucion.enable();
                self.verDistribucionFisica.enable();
                self.verPartidas.enable();
            } else {
                var verificarAccionesAE = function(sm) {
                    if (sm.hasSelection()) {
                        var rec = sm.getSelected();
                        if (rec.get('npartidas') > 0) {
                            self.verPartidas.enable();
                        } else {
                            self.verPartidas.disable();
                        }
                        self.editarAccion.enable();
                        self.eliminar.enable();
                    } else {
                        self.verPartidas.disable();
                        self.editarAccion.disable();
                        self.eliminar.disable();
                    }
                };
                self.store.on('datachanged', function(st) {
                    if (st.getCount() > 0) {
                        self.cargarPartida.enable();
                        self.descargarFormato.enable();
                        self.verDistribucion.enable();
                        self.verDistribucionFisica.enable();
                        self.cerrar.enable();
                        verificarAccionesAE(self.grid.getSelectionModel());
                    } else {
                        self.cargarPartida.disable();
                        //self.descargarFormato.disable();
                        self.verDistribucion.disable();
                        self.verDistribucionFisica.disable();
                        self.cerrar.disable();
                    }
                });
                self.rowSelModel.on('selectionchange', verificarAccionesAE);
            }
            this.store.load();
        },
    });
}(Ext, Reingsys, async));
