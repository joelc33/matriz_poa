<?php
$sql = "SELECT * FROM proyecto_seguimiento.tab_proyecto_responsable WHERE id_tab_proyecto='".$id_proyecto."';";
$result = $comunes->ObtenerFilasBySqlSelect($sql);
foreach($result as $key => $row){
	$data5 = json_encode(array(
		"co_proyecto_responsables"     => trim($row["id"]),
		"id_proyecto"     => trim($row["id_tab_proyecto"]),
		"nb_rp"     => trim($row["responsable_nombres"]),
		"nu_crp"     => trim($row["reponsable_cedula"]),
		"tx_mailrp"     => trim($row["responsable_correo"]),
		"tx_tfrp"     => trim($row["responsable_telefono"]),
		"nb_rt"     => trim($row["tecnico_nombres"]),
		"nu_crt"     => trim($row["tecnico_cedula"]),
		"tx_mailrt"     => trim($row["tecnico_correo"]),
		"tx_tfrt"     => trim($row["tecnico_telefono"]),
		"tx_utrt"     => trim($row["tecnico_unidad"]),
		"nb_rr"     => trim($row["registrador_nombres"]),
		"nu_crr"     => trim($row["registrador_cedula"]),
		"tx_mailrr"     => trim($row["registrador_correo"]),
		"tx_tfrr"     => trim($row["registrador_telefono"]),
		"nb_ra"     => trim($row["administrador_nombres"]),
		"nu_cra"     => trim($row["administrador_cedula"]),
		"tx_mailra"     => trim($row["administrador_correo"]),
		"tx_tfra"     => trim($row["administrador_telefono"]),
		"tx_uara"     => trim($row["administrador_unidad"]),
	));
}
?>
<script type="text/javascript">
Ext.ns("tabuladorCinco");
tabuladorCinco.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data5 ?>'});

//<ClavePrimaria>
this.co_proyecto_responsables = new Ext.form.Hidden({
	name:'co_proyecto_responsables',
	value:this.OBJ.co_proyecto_responsables
});
//</ClavePrimaria

this.nb_rp = new Ext.form.TextField({
	fieldLabel:'5.1. NOMBRE',
	name:'nb_rp',
	value:this.OBJ.nb_rp,
	width:400,
	maxLength: 80,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 80},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.nu_crp = new Ext.form.TextField({
	fieldLabel:'5.1.1. CÉDULA DE IDENTIDAD',
	name:'nu_crp',
	value:this.OBJ.nu_crp,
	width:200,
	maxLength: 12,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 12},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_mailrp = new Ext.form.TextField({
	fieldLabel:'5.1.2. CORREO ELECTRÓNICO',
	name:'tx_mailrp',
	value:this.OBJ.tx_mailrp,
	width:200,
	maxLength: 50,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 50},
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail',
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_tfrp = new Ext.form.TextField({
	fieldLabel:'5.1.3. NÚMERO TELEFÓNICO',
	name:'tx_tfrp',
	value:this.OBJ.tx_tfrp,
	width:200,
	maxLength: 14,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 14},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fieldset2 = new Ext.form.FieldSet({
	title: '5.1. RESPONSABLE DEL PROYECTO',
	autoWidth:true,
        items:[
			this.nb_rp,
			this.nu_crp,
			this.tx_mailrp,
			this.tx_tfrp
		]
});

this.nb_rt = new Ext.form.TextField({
	fieldLabel:'5.2. NOMBRE',
	name:'nb_rt',
	value:this.OBJ.nb_rt,
	width:400,
	maxLength: 80,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 80},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.nu_crt = new Ext.form.TextField({
	fieldLabel:'5.2.1. CÉDULA DE IDENTIDAD',
	name:'nu_crt',
	value:this.OBJ.nu_crt,
	width:200,
	maxLength: 12,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 12},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_mailrt = new Ext.form.TextField({
	fieldLabel:'5.2.2. CORREO ELECTRÓNICO',
	name:'tx_mailrt',
	value:this.OBJ.tx_mailrt,
	width:200,
	maxLength: 80,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 80},
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail',
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_tfrt = new Ext.form.TextField({
	fieldLabel:'5.2.3. NÚMERO TELEFÓNICO',
	name:'tx_tfrt',
	value:this.OBJ.tx_tfrt,
	width:200,
	maxLength: 14,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 14},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_utrt = new Ext.form.TextField({
	fieldLabel:'5.2.4. UNIDAD TÉCNICA',
	name:'tx_utrt',
	value:this.OBJ.tx_utrt,
	width:400,
	maxLength: 80,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 80},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fieldset3 = new Ext.form.FieldSet({
	title: '5.2. RESPONSABLE TÉCNICO',
	autoWidth:true,
        items:[
			this.nb_rt,
			this.nu_crt,
			this.tx_mailrt,
			this.tx_tfrt,
			this.tx_utrt
		]
});

this.nb_rr = new Ext.form.TextField({
	fieldLabel:'5.3. NOMBRE',
	name:'nb_rr',
	value:this.OBJ.nb_rr,
	width:400,
	maxLength: 80,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 80},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.nu_crr = new Ext.form.TextField({
	fieldLabel:'5.3.1. CÉDULA DE IDENTIDAD',
	name:'nu_crr',
	value:this.OBJ.nu_crr,
	width:200,
	maxLength: 12,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 12},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_mailrr = new Ext.form.TextField({
	fieldLabel:'5.3.2. CORREO ELECTRÓNICO',
	name:'tx_mailrr',
	value:this.OBJ.tx_mailrr,
	width:200,
	maxLength: 80,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 80},
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail',
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_tfrr = new Ext.form.TextField({
	fieldLabel:'5.3.3. NÚMERO TELEFÓNICO',
	name:'tx_tfrr',
	value:this.OBJ.tx_tfrr,
	width:200,
	maxLength: 14,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 14},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fieldset4 = new Ext.form.FieldSet({
	title: '5.3. REGISTRADOR',
	autoWidth:true,
        items:[
			this.nb_rr,
			this.nu_crr,
			this.tx_mailrr,
			this.tx_tfrr,
		]
});

this.nb_ra = new Ext.form.TextField({
	fieldLabel:'5.4. NOMBRE',
	name:'nb_ra',
	value:this.OBJ.nb_ra,
	width:400,
	maxLength: 80,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 80},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.nu_cra = new Ext.form.TextField({
	fieldLabel:'5.4.1. CÉDULA DE IDENTIDAD',
	name:'nu_cra',
	value:this.OBJ.nu_cra,
	width:200,
	maxLength: 12,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 12},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_mailra = new Ext.form.TextField({
	fieldLabel:'5.4.2. CORREO ELECTRÓNICO',
	name:'tx_mailra',
	value:this.OBJ.tx_mailra,
	width:200,
	maxLength: 80,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 80},
	regex:/^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
	regexText:'Este campo debe contener direcciones de correo electrónico válidas únicas o múltiples separadas por punto y coma (;)',
	blankText : 'ingresar direccion de e-mail',
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_tfra = new Ext.form.TextField({
	fieldLabel:'5.4.3. NÚMERO TELEFÓNICO',
	name:'tx_tfra',
	value:this.OBJ.tx_tfra,
	width:200,
	maxLength: 14,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 14},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_uara = new Ext.form.TextField({
	fieldLabel:'5.4.4. UNIDAD ADMINISTRADORA',
	name:'tx_uara',
	value:this.OBJ.tx_uara,
	width:400,
	maxLength: 80,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 80},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fieldset5 = new Ext.form.FieldSet({
	title: '5.4. RESPONSABLE ADMINSTRATIVO',
	autoWidth:true,
        items:[
			this.nb_ra,
			this.nu_cra,
			this.tx_mailra,
			this.tx_tfra,
			this.tx_uara
		]
});

this.fieldset1 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
		this.fieldset2,
		this.fieldset3,
		this.fieldset4,
		this.fieldset5
		]
});

this.panelDatos = new Ext.Panel({
    title: '5. RESPONSABLES DEL PROYECTO',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
		this.co_proyecto_responsables,
		this.fieldset1
	]
});

}
};
Ext.onReady(tabuladorCinco.main.init, tabuladorCinco.main);
</script>
