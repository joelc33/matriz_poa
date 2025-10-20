<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	http_response_code(403);
	die();
}  
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

$codigo = decode($_POST['codigo']);
if($codigo!=''||$codigo!=null){
	$sql = "SELECT * FROM mantenimiento.tab_ejecutores where id=".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_ejecutores"     => trim($row["id"]),
			"id_ejecutor"     => trim($row["id_ejecutor"]),
			"tx_ejecutor"     => trim($row["tx_ejecutor"]),
			"car_01"     => trim($row["car_01"]),
			"car_02"     => trim($row["car_02"]),
			"car_03"     => trim($row["car_03"]),
			"car_04"     => trim($row["car_04"]),
			"co_tipo_ejecutor"     => trim($row["id_tab_tipo_ejecutor"]),
			"id_ambito_ejecutor"     => trim($row["id_tab_ambito_ejecutor"]),
			"codigo_01"     => trim($row["codigo_01"]),
			"codigo_eje"     => trim($row["codigo_eje"]),
			"edo_reg"     => trim($row["in_activo"]),
			"de_correo"     => trim($row["de_correo"]),
			"de_telefono"     => trim($row["de_telefono"]),
		));
	}
}else{
	$data = json_encode(array(
		"co_ejecutores"     => "",
		"id_ejecutor"     => "",
		"tx_ejecutor"     => "",
		"car_01"    => "",
		"car_02"     => "",
		"car_03"     => "",
		"car_04"     => "",
		"co_tipo_ejecutor"     => "",
		"id_ambito_ejecutor"     => "",
		"codigo_01"     => "",
		"codigo_eje"     => "",
		"edo_reg"     => "",
		"de_correo"     => "",
		"de_telefono"     => "",
	));
}
?>
<script type="text/javascript">
Ext.ns("unidadEjecutoraEditar");
unidadEjecutoraEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<Stores de fk>
this.storeCO_TIPO_EJECUTOR = this.getStoreCO_TIPO_EJECUTOR();
//<Stores de fk>
//<Stores de fk>
this.storeID_AMBITO_EJECUTOR = this.getStoreID_AMBITO_EJECUTOR();
//<Stores de fk>

//<ClavePrimaria>
this.co_ejecutores = new Ext.form.Hidden({
    name:'co_ejecutores',
    value:this.OBJ.co_ejecutores});
//</ClavePrimaria>

this.id_ejecutor = new Ext.form.TextField({
	fieldLabel:'Codigo',
	name:'id_ejecutor',
	value:this.OBJ.id_ejecutor,
	width:100,
	maxLength: 4,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4},
	readOnly:(this.OBJ.id_ejecutor!='')?true:false,
	style:(this.OBJ.id_ejecutor!='')?'background:#c9c9c9;':'',
});

this.tx_ejecutor = new Ext.form.TextField({
	fieldLabel:'Nombre',
	name:'tx_ejecutor',
	value:this.OBJ.tx_ejecutor,
	width:400,
	maxLength: 250,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.car_01 = new Ext.form.TextField({
	fieldLabel:'car_01',
	name:'car_01',
	value:this.OBJ.car_01,
	width:100,
	maxLength: 4,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.car_02 = new Ext.form.TextField({
	fieldLabel:'car_02',
	name:'car_02',
	value:this.OBJ.car_02,
	width:100,
	maxLength: 4,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.car_03 = new Ext.form.TextField({
	fieldLabel:'car_03',
	name:'car_03',
	value:this.OBJ.car_03,
	width:100,
	maxLength: 4,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.car_04 = new Ext.form.TextField({
	fieldLabel:'car_04',
	name:'car_04',
	value:this.OBJ.car_04,
	width:100,
	maxLength: 4,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.co_tipo_ejecutor = new Ext.form.ComboBox({
	fieldLabel:'Tipo de Ejecutor',
	store: this.storeCO_TIPO_EJECUTOR,
	typeAhead: true,
	valueField: 'co_tipo_ejecutor',
	displayField:'tx_tipo_ejecutor',
	hiddenName:'co_tipo_ejecutor',
	//readOnly:(this.OBJ.co_tipo_ejecutor!='')?true:false,
	//style:(this.main.OBJ.co_tipo_ejecutor!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione tipo Ejecutor',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	allowBlank:false
});
this.storeCO_TIPO_EJECUTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_tipo_ejecutor,
	value:  this.OBJ.co_tipo_ejecutor,
	objStore: this.storeCO_TIPO_EJECUTOR
});

this.id_ambito_ejecutor = new Ext.form.ComboBox({
	fieldLabel:'Ambito del Ejecutor',
	store: this.storeID_AMBITO_EJECUTOR,
	typeAhead: true,
	valueField: 'id_ambito_ejecutor',
	displayField:'tx_ambito_ejecutor',
	hiddenName:'id_ambito_ejecutor',
	//readOnly:(this.OBJ.id_ambito_ejecutor!='')?true:false,
	//style:(this.main.OBJ.id_ambito_ejecutor!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Ambito Ejecutor',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	allowBlank:false
});
this.storeID_AMBITO_EJECUTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.id_ambito_ejecutor,
	value:  this.OBJ.id_ambito_ejecutor,
	objStore: this.storeID_AMBITO_EJECUTOR
});

this.codigo_01 = new Ext.form.TextField({
	fieldLabel:'codigo_01',
	name:'codigo_01',
	value:this.OBJ.codigo_01,
	width:100,
	maxLength: 8,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 8},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.codigo_eje = new Ext.form.TextField({
	fieldLabel:'codigo_eje',
	name:'codigo_eje',
	value:this.OBJ.codigo_eje,
	width:100,
	maxLength: 4,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 4},
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.de_correo = new Ext.form.TextField({
	fieldLabel:'Correo Institucional',
	name:'de_correo',
	value:this.OBJ.de_correo,
	width:200,
	//allowBlank:false,
	emptyText: 'correo@diminio.com',
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail'
});

this.de_telefono = new Ext.form.TextField({
	fieldLabel:'Telefono',
	name:'de_telefono',
	value:this.OBJ.de_telefono,
	//allowBlank:false,
	//emptyText: '0000-0000000',
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fielset1 = new Ext.form.FieldSet({
              title:'Datos del Registro',width:670,
              items:[
		this.id_ejecutor,
		this.tx_ejecutor,
		this.car_01,
		this.car_02,
		this.car_03,
		this.car_04,
		this.co_tipo_ejecutor,
		this.id_ambito_ejecutor,
		this.codigo_01,
		this.codigo_eje,
		this.de_correo,
		this.de_telefono
]});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!unidadEjecutoraEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        unidadEjecutoraEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/unidadEjecutora/funcion.php?op=2',
            waitMsg: 'Enviando datos, por favor espere..',
            waitTitle:'Enviando',
            failure: function(form, action) {
                Ext.MessageBox.alert('Error en transacción', action.result.msg);
            },
            success: function(form, action) {
                 if(action.result.success){
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
                 unidadEjecutoraLista.main.store_lista.load();
                 unidadEjecutoraEditar.main.winformPanel_.close();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        unidadEjecutoraEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	//fileUpload: true,
	width:700,
	autoHeight:true,  
	autoScroll:true,
	labelWidth: 180,
	border:false,
	bodyStyle:'padding:10px;',
	items:[
		this.co_ejecutores,
		this.fielset1,
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Unidad Ejecutora',
    modal:true,
    constrain:true,
width:714,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
<?php if( in_array( array( 'de_privilegio' => 'ejecutor.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.guardar,
<?php } ?>
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
unidadEjecutoraLista.main.mascara.hide();
},
getStoreCO_TIPO_EJECUTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/unidadEjecutora/funcion.php?op=3',
        root:'data',
        fields:[
            {name: 'co_tipo_ejecutor'},{name: 'tx_tipo_ejecutor'}
            ]
    });
    return this.store;
},
getStoreID_AMBITO_EJECUTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/unidadEjecutora/funcion.php?op=4',
        root:'data',
        fields:[
            {name: 'id_ambito_ejecutor'},{name: 'tx_ambito_ejecutor'}
            ]
    });
    return this.store;
}
};
Ext.onReady(unidadEjecutoraEditar.main.init, unidadEjecutoraEditar.main);
</script>
