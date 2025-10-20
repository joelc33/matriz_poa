<?php        
session_start(); 
if( $_SESSION['estatus'] !== 'OK' ) {
    http_response_code(403);
	die();
} 
include("../../configuracion/ConexionComun.php");
$comunes = new ConexionComun();

$codigo = decode($_POST['codigo']);
if($codigo!=''||$codigo!=null){
	$sql = "SELECT t01.id as co_usuario, t29.id as co_funcionario, da_login, remember_token, 
	id_tab_documento, nu_cedula, nb_funcionario, ap_funcionario, 
       	id_tab_ejecutores, id_tab_cargo, tx_direccion, tx_telefono, tx_email, t01.in_estatus, id_tab_rol
	FROM autenticacion.tab_usuarios as t01
	inner join mantenimiento.tab_funcionario as t29 on t01.id=t29.id_tab_usuarios
	inner join autenticacion.tab_usuario_rol as t02 on t01.id = t02.id_tab_usuarios
	where t01.id =".$codigo;
	$result = $comunes->ObtenerFilasBySqlSelect($sql);
	foreach($result as $key => $row){
		$data = json_encode(array(
			"co_usuario"     => trim($row["co_usuario"]),
			"tx_login"     => trim($row["da_login"]),
			"co_rol"     => trim($row["id_tab_rol"]),
			"co_funcionario"     => trim($row["co_funcionario"]),
			"edo_reg"     => trim($row["in_estatus"]),
			"tx_password"     => trim($row["remember_token"]),
			"co_documento" => trim($row["id_tab_documento"]),
			"nu_cedula" => trim($row["nu_cedula"]),
			"nb_funcionario" => trim($row["nb_funcionario"]),
			"ap_funcionario" => trim($row["ap_funcionario"]),
			"co_ejecutores" => trim($row["id_tab_ejecutores"]),
			"co_cargo" => trim($row["id_tab_cargo"]),
			"tx_direccion" => trim($row["tx_direccion"]),
			"tx_telefono" => trim($row["tx_telefono"]),
			"tx_email" => trim($row["tx_email"]),
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
this.storeCO_ROL = this.getStoreCO_ROL();
//<Stores de fk>
//<Stores de fk>
this.storeID_EJECUTOR = this.getStoreID_EJECUTOR();
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

this.tx_login = new Ext.form.TextField({
	fieldLabel:'Usuario',
	name:'tx_login',
	value:this.OBJ.tx_login,
	allowBlank:false,
	width:200
});

this.tx_password = new Ext.form.TextField({
	fieldLabel:'Contraseña',
	inputType:'password',
	name: 'tx_password',
	value:this.OBJ.tx_password,
	id:'tx_password',
	allowBlank:false,
	maxLength:60,
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

this.co_rol = new Ext.form.ComboBox({
	fieldLabel:'Rol',
	store: this.storeCO_ROL,
	typeAhead: true,
	valueField: 'co_rol',
	displayField:'tx_rol',
	hiddenName:'co_rol',
	//readOnly:(this.OBJ.co_rol!='')?true:false,
	//style:(this.main.OBJ.co_rol!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Perfil',
	selectOnFocus: true,
	mode: 'local',
	width:200,
	resizable:true,
	allowBlank:false
});
this.storeCO_ROL.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_rol,
	value:  this.OBJ.co_rol,
	objStore: this.storeCO_ROL
});

this.co_ejecutores = new Ext.form.ComboBox({
	fieldLabel:'Unidad Ejecutora',
	store: this.storeID_EJECUTOR,
	typeAhead: true,
	valueField: 'co_ejecutores',
	displayField:'tx_ejecutor',
	hiddenName:'co_ejecutores',
	//readOnly:(this.OBJ.co_ejecutores!='')?true:false,
	//style:(this.main.OBJ.co_ejecutores!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Unidad Ejecutora',
	selectOnFocus: true,
	mode: 'local',
	width:350,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_ejecutor}</div></div></tpl>'),
	//listWidth:'600',
	resizable:true,
	allowBlank:false
});
this.storeID_EJECUTOR.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_ejecutores,
	value:  this.OBJ.co_ejecutores,
	objStore: this.storeID_EJECUTOR
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
	width:350,
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
		this.tx_password,
		this.co_rol
		]
});

this.fieldset2 = new Ext.form.FieldSet({
	title: 'Datos del Funcionario',
	autoWidth:true,
        items:[
		this.compositefieldCI,
		this.nb_funcionario,
		this.ap_funcionario,
		this.co_ejecutores,
		this.co_cargo,
		this.tx_direccion,
		this.tx_telefono,
		this.tx_email
		]
});

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
            url:'formulacion/modulos/usuario/funcion.php?op=7',
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
                 usuarioLista.main.store_lista.load();
                 usuarioEditar.main.winformPanel_.close();
             }
        });

   
    }
});

this.salir = new Ext.Button({
    text:'Salir',
//    iconCls: 'icon-cancelar',
    handler:function(){
        usuarioEditar.main.winformPanel_.close();
    }
});

this.formPanel_ = new Ext.form.FormPanel({
	//frame:true,
	width:600,
	labelWidth: 150,
	border:false,
	autoHeight:true,  
	autoScramol:true,
	bodyStyle:'padding:10px;',
	items:[
		this.co_usuario,
		this.co_funcionario,
		this.fieldset1,
		this.fieldset2
	]
});

this.winformPanel_ = new Ext.Window({
    title:'Formulario: Usuario',
    modal:true,
    constrain:true,
width:614,
    frame:true,
    closabled:true,
    autoHeight:true,
    items:[
        this.formPanel_
    ],
    buttons:[
<?php if( in_array( array( 'de_privilegio' => 'usuarios.guardar', 'in_habilitado' => true), $_SESSION['spe_session'][0][0] )){ ?>
        this.guardar,
<?php } ?>
        this.salir
    ],
    buttonAlign:'center'
});
this.winformPanel_.show();
usuarioLista.main.mascara.hide();
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
getStoreCO_ROL:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/usuario/funcion.php?op=4',
        root:'data',
        fields:[
            {name: 'co_rol'},{name: 'tx_rol'}
            ]
    });
    return this.store;
},
getStoreID_EJECUTOR:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/usuario/funcion.php?op=5',
        root:'data',
        fields:[
            {name: 'co_ejecutores'},{name: 'tx_ejecutor'}
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
