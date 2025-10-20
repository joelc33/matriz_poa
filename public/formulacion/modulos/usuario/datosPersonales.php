<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}   
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

$codigo = decode($_SESSION['co_usuario']);
if($codigo!=''||$codigo!=null){
	$sql = "SELECT t01.id as co_usuario, da_login, t29.id as co_funcionario, id_tab_documento,
	nu_cedula, nb_funcionario, ap_funcionario, 
       	id_tab_ejecutores, id_tab_cargo, tx_direccion, tx_telefono, tx_email, 
       	t01.in_estatus, de_correo, de_telefono
	FROM autenticacion.tab_usuarios as t01
	inner join mantenimiento.tab_funcionario as t29 on t01.id=t29.id_tab_usuarios
	inner join mantenimiento.tab_ejecutores as t30 on t29.id_tab_ejecutores=t30.id
	where t01.id = ".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_usuario"     => trim($row["co_usuario"]),
			"tx_login"     => trim($row["da_login"]),
			"co_rol"     => trim($row["co_rol"]),
			"co_funcionario"     => trim($row["co_funcionario"]),
			"edo_reg"     => trim($row["in_estatus"]),
			"co_documento" => trim($row["id_tab_documento"]),
			"nu_cedula" => trim($row["nu_cedula"]),
			"nb_funcionario" => trim($row["nb_funcionario"]),
			"ap_funcionario" => trim($row["ap_funcionario"]),
			"co_ejecutores" => trim($row["id_tab_ejecutores"]),
			"co_cargo" => trim($row["id_tab_cargo"]),
			"tx_direccion" => trim($row["tx_direccion"]),
			"tx_telefono" => trim($row["tx_telefono"]),
			"tx_email" => trim($row["tx_email"]),
			"de_correo"     => trim($row["de_correo"]),
			"de_telefono"     => trim($row["de_telefono"]),
		));
	}
}else{
	$data = json_encode(array(
		"co_usuario"     => "",
		"tx_login"     => "",
		"co_rol"     => "",
		"co_funcionario"     => "",
		"edo_reg"     => "",
		"tx_password"     => "",
		"co_documento" => "",
		"nu_cedula" => "",
		"nb_funcionario" => "",
		"ap_funcionario" => "",
		"co_ejecutores" => "",
		"co_cargo" => "",
		"tx_direccion" => "",
		"tx_telefono" => "",
		"tx_email" => "",
	));
}
?>
<script type="text/javascript">
Ext.ns("usuarioEditar");
usuarioEditar.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<Stores de fk>
this.storeCO_DOCUMENTO = this.getStoreCO_DOCUMENTO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_CARGO = this.getStoreCO_CARGO();
//<Stores de fk>

//<ClavePrimaria>
this.co_usuario = new Ext.form.Hidden({
    name:'co_usuario',
    value:this.OBJ.co_usuario});
//</ClavePrimaria>

//<ClavePrimaria>
this.co_funcionario = new Ext.form.Hidden({
    name:'co_funcionario',
    value:this.OBJ.co_funcionario});
//</ClavePrimaria>

//<ClavePrimaria>
this.co_ejecutores = new Ext.form.Hidden({
    name:'co_ejecutores',
    value:this.OBJ.co_ejecutores});
//</ClavePrimaria>


this.tx_login = new Ext.form.TextField({
	fieldLabel:'Usuario',
	name:'tx_login',
	value:this.OBJ.tx_login,
	readOnly:true,
	style:'background:#c9c9c9;',
	allowBlank:false,
	width:200
});

this.co_documento = new Ext.form.ComboBox({
	fieldLabel:'documento',
	store: this.storeCO_DOCUMENTO,
	typeAhead: true,
	valueField: 'co_documento',
	displayField:'inicial',
	hiddenName:'co_documento',
	//readOnly:(this.OBJ.co_documento!='')?true:false,
	//style:(this.main.OBJ.co_documento!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'...',
	selectOnFocus: true,
	mode: 'local',
	width:40,
	resizable:true,
	allowBlank:false
});

this.storeCO_DOCUMENTO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_documento,
	value:  this.OBJ.co_documento,
	objStore: this.storeCO_DOCUMENTO
});

this.nu_cedula = new Ext.form.NumberField({
	fieldLabel:'Nu cedula',
	name:'nu_cedula',
	value:this.OBJ.nu_cedula,
	width:155,
	allowBlank:false,
	minLength : 5,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 8},
});

this.compositefieldCI = new Ext.form.CompositeField({
fieldLabel: 'Cedula',
items: [
	this.co_documento,
	this.nu_cedula,
	]
});

this.nb_funcionario = new Ext.form.TextField({
	fieldLabel:'Nombre',
	name:'nb_funcionario',
	value:this.OBJ.nb_funcionario,
	allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.ap_funcionario = new Ext.form.TextField({
	fieldLabel:'Apellido',
	name:'ap_funcionario',
	value:this.OBJ.ap_funcionario,
	allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.co_cargo = new Ext.form.ComboBox({
	fieldLabel:'Cargo',
	store: this.storeCO_CARGO,
	typeAhead: true,
	valueField: 'co_cargo',
	displayField:'tx_cargo',
	hiddenName:'co_cargo',
	//readOnly:(this.OBJ.co_cargo!='')?true:false,
	//style:(this.main.OBJ.co_cargo!='')?'background:#c9c9c9;':'',
	//forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Cargo',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_cargo}</div></div></tpl>'),
	resizable:true,
	allowBlank:false
});
this.storeCO_CARGO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_cargo,
	value:  this.OBJ.co_cargo,
	objStore: this.storeCO_CARGO
});

this.tx_direccion = new Ext.form.TextArea({
	fieldLabel:'Direccion',
	name:'tx_direccion',
	value:this.OBJ.tx_direccion,
	//allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_telefono = new Ext.form.TextField({
	fieldLabel:'Telefonos',
	name:'tx_telefono',
	value:this.OBJ.tx_telefono,
	//allowBlank:false,
	width:200,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_email = new Ext.form.TextField({
	fieldLabel:'Correo Electrónico',
	name:'tx_email',
	value:this.OBJ.tx_email,
	width:200,
	//allowBlank:false,
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail',
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fieldset1 = new Ext.form.FieldSet({
	title: 'Datos de Cuenta',
	autoWidth:true,
        items:[
		this.tx_login,
		]
});

this.fieldset2 = new Ext.form.FieldSet({
	title: 'Datos del Funcionario',
	autoWidth:true,
        items:[
		this.compositefieldCI,
		this.nb_funcionario,
		this.ap_funcionario,
		this.co_cargo,
		this.tx_direccion,
		this.tx_telefono,
		this.tx_email
		]
});

this.de_correo = new Ext.form.TextField({
	fieldLabel:'Correo Institucional',
	name:'de_correo',
	value:this.OBJ.de_correo,
	width:300,
	allowBlank:false,
	emptyText: 'correo@diminio.com',
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail'
});

this.de_telefono = new Ext.form.TextField({
	fieldLabel:'Telefono',
	name:'de_telefono',
	value:this.OBJ.de_telefono,
	allowBlank:false,
	//emptyText: '0000-0000000',
	width:300,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fielset3 = new Ext.form.FieldSet({
              title:'Datos de contacto del Ejecutor',autoWidth:true,
		labelWidth: 130,
              items:[
		this.co_ejecutores,
		this.de_correo,
		this.de_telefono
]});

this.guardar = new Ext.Button({
    text:'Guardar',
    iconCls: 'icon-guardar',
    handler:function(){

        if(!usuarioEditar.main.formPanel_.getForm().isValid()){
            Ext.Msg.alert("Alerta","Debe ingresar los campos en rojo");
            return false;
        }
        usuarioEditar.main.formPanel_.getForm().submit({
            method:'POST',
            url:'formulacion/modulos/usuario/funcion.php?op=8',
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
		this.panelCambio = Ext.getCmp('tabpanel');
		this.panelCambio.remove('24');
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Cerrar',
    iconCls: 'icon-cancelar',
    handler:function(){
	this.panelCambio = Ext.getCmp('tabpanel');
	this.panelCambio.remove('24');
    }
});

this.formPanel_ = new Ext.form.FormPanel({
    autoWidth:true,
    border:false,
    labelWidth: 160,
    padding:'10px',
    deferredRender: false,
	items:[
		this.co_usuario,
		this.co_funcionario,
		this.fieldset1,
		this.fieldset2,
		this.fielset3
	],
    buttonAlign:'left',
    buttons:[
<?php if( in_array( array( 'de_privilegio' => 'datospersonales.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.guardar,
<?php } ?>
	this.salir
    ]
});

this.formPanel_.render("datosPersonales");
},
getStoreCO_DOCUMENTO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/usuario/funcion.php?op=3',
        root:'data',
        fields:[
            {name: 'co_documento'},{name: 'inicial'}
            ]
    });
    return this.store;
},
getStoreCO_CARGO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/usuario/funcion.php?op=6',
        root:'data',
        fields:[
            {name: 'co_cargo'},{name: 'tx_cargo'}
            ]
    });
    return this.store;
}
};
Ext.onReady(usuarioEditar.main.init, usuarioEditar.main);
</script>
<div id="datosPersonales"></div>
