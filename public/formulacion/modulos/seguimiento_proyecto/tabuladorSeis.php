<?php
$sql = "SELECT * FROM proyecto_seguimiento.tab_proyecto_alcance WHERE id_tab_proyecto='".$id_proyecto."';";
$result = $comunes->ObtenerFilasBySqlSelect($sql);
foreach($result as $key => $row){
	$data6 = json_encode(array(
		"co_proyecto_alcance"     => trim($row["id"]),
		"id_proyecto"     => trim($row["id_tab_proyecto"]),
		"tx_epn"     => trim($row["enunciado_inicial"]),
		"tx_pa"     => trim($row["poblacion_afectada"]),
		"tx_isi"     => trim($row["indicador_inicial"]),
		"tx_fi"     => trim($row["formula_indicador"]),
		"tx_fuentei"     => trim($row["fuente_indicador"]),
		"fecha_inisi"     => trim($row["fecha_sit_inicial"]),
		"tx_esd"     => trim($row["enunciado_deseado"]),
		"tx_po"     => trim($row["poblacion_objetivo"]),
		"tx_isd"     => trim($row["indicador_deseado"]),
		"tx_rebs"     => trim($row["resultado_esperado"]),
		"co_unidades_medida"     => trim($row["id_tab_unidad_medida"]),
		"mo_meta_proy"     => trim($row["meta"]),
		"nu_benf"     => trim($row["benef_femeninos"]),
		"nu_benm"     => trim($row["benef_masculinos"]),
		"tx_demb"     => trim($row["denominacion_benef"]),
		"nu_benp"     => trim($row["benef_femeninos"]+$row["benef_masculinos"]),
		"nu_tedf"     => trim($row["emp_dir_feme"]),
		"nu_tedm"     => trim($row["emp_dir_mascu"]),
		"nu_tedmf"     => trim($row["emp_dir_feme"]+$row["emp_dir_mascu"]),
		"nu_tednf"     => trim($row["emp_new_feme"]),
		"nu_tednm"     => trim($row["emp_new_mascu"]),
		"nu_tednmf"     => trim($row["emp_new_feme"]+$row["emp_new_mascu"]),
		"nu_tedsf"     => trim($row["emp_sos_feme"]),
		"nu_tedsm"     => trim($row["emp_sos_mascu"]),
		"nu_tedsmf"     => trim($row["emp_sos_feme"]+$row["emp_sos_mascu"]),
		"in_pvo"     => trim($row["proy_vincu_otro"]?$row["proy_vincu_otro"]:'NO'),
		"co_vinculo_proyecto"     => trim($row["id_si_es_si"]),
		"tx_nipdv"     => trim($row["inst_responsable"]),
		"tx_nirpv"     => trim($row["instancia_responsable"]),
		"tx_nipcv"     => trim($row["nombre_proy"]),
		"tx_eqmvp"     => trim($row["medida_vinculo"]),
		"tx_re_esperado"     => trim($row["tx_re_esperado"]),
	));
}
?>
<script type="text/javascript">
Ext.ns("tabuladorSeis");
tabuladorSeis.main = {
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data6 ?>'});

//<ClavePrimaria>
this.co_proyecto_alcance = new Ext.form.Hidden({
	name:'co_proyecto_alcance',
	value:this.OBJ.co_proyecto_alcance
});
//</ClavePrimaria

//<Stores de fk>
this.storeCO_VINCULO_PROYECTO = this.getStoreCO_VINCULO_PROYECTO();
//<Stores de fk>
//<Stores de fk>
this.storeCO_UNIDADES_MEDIDA = this.getStoreCO_UNIDADES_MEDIDA();
//<Stores de fk>

this.tx_epn = new Ext.form.TextArea({
	fieldLabel:'6.1.1.1. Enunciado del problema o necesidad',
	name:'tx_epn',
	value:this.OBJ.tx_epn,
	width:400,
	maxLength: 200,
	//autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 200},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_pa = new Ext.form.TextField({
	fieldLabel:'6.1.1.2. Población afectada',
	name:'tx_pa',
	value:this.OBJ.tx_pa,
	width:400,
	maxLength: 150,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 150},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_isi = new Ext.form.TextField({
	fieldLabel:'6.1.1.3. Indicador de la Situación Inicial',
	name:'tx_isi',
	value:this.OBJ.tx_isi,
	width:400,
	maxLength: 150,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 150},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_fi = new Ext.form.TextField({
	fieldLabel:'6.1.1.4. Fórmula del Indicador',
	name:'tx_fi',
	value:this.OBJ.tx_fi,
	width:400,
	maxLength: 150,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 150},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_fuentei = new Ext.form.TextField({
	fieldLabel:'6.1.1.5. Fuente del Indicador',
	name:'tx_fuentei',
	value:this.OBJ.tx_fuentei,
	width:400,
	maxLength: 150,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 150},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fecha_inisi = new Ext.form.DateField({
	fieldLabel:'6.1.1.6. Fecha del Indicador de la Situación Inicial',
	name:'fecha_inisi',
	value:(this.OBJ.fecha_inisi)?this.OBJ.fecha_inisi:'<?= date('d-m-Y',strtotime($fechaI)); ?>',
	//allowBlank:false,
	width:100,
	//minValue:'<?php echo $fechaI; ?>',
	//maxValue:'<?php echo $fechaF; ?>',
});

this.fieldset1 = new Ext.form.FieldSet({
    	title: '6.1.1. SITUACIÓN INICIAL',
	autoWidth:true,
        items:[
		this.co_proyecto_alcance,
		this.tx_epn,
		this.tx_pa,
		this.tx_isi,
		this.tx_fi,
		this.tx_fuentei,
		this.fecha_inisi
		]
});

this.tx_esd = new Ext.form.TextArea({
	fieldLabel:'6.1.2.1. Enunciado de la Situación Deseada',
	name:'tx_esd',
	value:this.OBJ.tx_esd,
	width:400,
	maxLength: 200,
	//autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 200},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_po = new Ext.form.TextField({
	fieldLabel:'6.1.2.2. Población Objetivo',
	name:'tx_po',
	value:this.OBJ.tx_po,
	width:400,
	maxLength: 150,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 150},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_isd = new Ext.form.TextField({
	fieldLabel:'6.1.2.3. Indicador de la Situación Deseada',
	name:'tx_isd',
	value:this.OBJ.tx_isd,
	width:400,
	maxLength: 150,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 150},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_rebs = new Ext.form.TextField({
	fieldLabel:'6.1.2.4. Resultado Esperado (Bien o Servicio)',
	name:'tx_rebs',
	value:this.OBJ.tx_rebs,
	width:400,
	maxLength: 150,
	autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 150},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.co_unidades_medida = new Ext.form.ComboBox({
	fieldLabel:'6.1.2.5. Unidad de Medida',
	store: this.storeCO_UNIDADES_MEDIDA,
	typeAhead: true,
	valueField: 'co_unidades_medida',
	displayField:'tx_unidades_medida',
	hiddenName:'co_unidades_medida',
	//readOnly:(this.OBJ.co_unidades_medida!='')?true:false,
	//style:(this.OBJ.co_unidades_medida!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione Unidades',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	resizable:true,
	//allowBlank:false
});

this.storeCO_UNIDADES_MEDIDA.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_unidades_medida,
	value:  this.OBJ.co_unidades_medida,
	objStore: this.storeCO_UNIDADES_MEDIDA
});

this.mo_meta_proy = new Ext.form.NumberField({
	fieldLabel:'6.1.2.6. Meta del Proyecto',
	name:'mo_meta_proy',
	value:this.OBJ.mo_meta_proy,
	//allowBlank:false,
	width:100,
	minLength : 1,
	maxLength: 10,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 10},
});

this.tx_re_esperado = new Ext.form.TextArea({
	fieldLabel:'6.1.2.7. Resultados Esperados',
	name:'tx_re_esperado',
	value:this.OBJ.tx_re_esperado,
	width:400,
	maxLength: 200,
	//autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 200},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fieldset2 = new Ext.form.FieldSet({
    	title: '6.1.2. SITUACIÓN DESEADA',
	autoWidth:true,
        items:[
		this.tx_esd,
		this.tx_po,
		this.tx_isd,
		this.tx_rebs,
		this.co_unidades_medida,
		this.mo_meta_proy,
		this.tx_re_esperado
		]
});

this.panelDatos61 = new Ext.Panel({
    title: '6.1. SITUACIÓN INICIAL Y DESEADA DEL PROYECTO',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
		this.fieldset1,
		this.fieldset2
	]
});

this.nu_benf = new Ext.form.NumberField({
	fieldLabel:'6.2.1. N° Beneficiarios Femeninos',
	name:'nu_benf',
	value:this.OBJ.nu_benf,
	//allowBlank:false,
	width:100,
	minLength : 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 7},
	validationEvent: 'blur',
	emptyText: '0',
	decimalPrecision: 0,
	allowNegative: false,
   	style: 'text-align: right',
	validator: function(value){
		benf=value;
        	if(isNaN(benf)){benf = parseFloat(0);}
		benm=tabuladorSeis.main.nu_benm.getValue();
        	if(isNaN(benm)){benm = parseFloat(0);}
		tabuladorSeis.main.nu_benp.setValue(parseFloat(benf)+parseFloat(benm));
	}
});

this.nu_benm = new Ext.form.NumberField({
	fieldLabel:'6.2.2. N° Beneficiarios Masculinos',
	name:'nu_benm',
	value:this.OBJ.nu_benm,
	//allowBlank:false,
	width:100,
	minLength : 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 7},
	validationEvent: 'blur',
	emptyText: '0',
	decimalPrecision: 0,
	allowNegative: false,
   	style: 'text-align: right',
	validator: function(value){
		benm=value;
        	if(isNaN(benm)){benm = parseFloat(0);}
		benf=tabuladorSeis.main.nu_benf.getValue();
        	if(isNaN(benf)){benf = parseFloat(0);}
		tabuladorSeis.main.nu_benp.setValue(parseFloat(benf)+parseFloat(benm));
	}
});

this.tx_demb = new Ext.form.TextField({
	fieldLabel:'Denominación del beneficiario',
	name:'tx_demb',
	value:this.OBJ.tx_demb,
	width:400,
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.nu_benp = new Ext.form.NumberField({
	fieldLabel:'<b>6.2.3. Total Beneficiarios del Proyecto</b>',
	name:'nu_benp',
	value:this.OBJ.nu_benp,
	//allowBlank:false,
	width:100,
	minLength : 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 8},
	emptyText: '0',
	decimalPrecision: 0,
	allowNegative: false,
   	style: 'text-align: right',
	readOnly:true,
});

this.fieldset3 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
		this.nu_benf,
		this.nu_benm,
		this.nu_benp,
		this.tx_demb
		]
});

this.nu_tedf = new Ext.form.NumberField({
	fieldLabel:'6.3.1. Total empleos directos femeninos',
	name:'nu_tedf',
	value:this.OBJ.nu_tedf,
	//allowBlank:false,
	width:100,
	minLength : 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 7},
	validationEvent: 'blur',
	emptyText: '0',
	decimalPrecision: 0,
	allowNegative: false,
   	style: 'text-align: right',
	validator: function(value){
		tedf=value;
        	if(isNaN(tedf)){tedf = parseFloat(0);}
		tedm=tabuladorSeis.main.nu_tedm.getValue();
        	if(isNaN(benm)){tedm = parseFloat(0);}
		tabuladorSeis.main.nu_tedmf.setValue(parseFloat(tedf)+parseFloat(tedm));
	}
});

this.nu_tedm = new Ext.form.NumberField({
	fieldLabel:'6.3.2. Total empleos directos masculinos',
	name:'nu_tedm',
	value:this.OBJ.nu_tedm,
	//allowBlank:false,
	width:100,
	minLength : 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 7},
	validationEvent: 'blur',
	emptyText: '0',
	decimalPrecision: 0,
	allowNegative: false,
   	style: 'text-align: right',
	validator: function(value){
		tedm=value;
        	if(isNaN(tedm)){tedm = parseFloat(0);}
		tedf=tabuladorSeis.main.nu_tedf.getValue();
        	if(isNaN(tedf)){tedf = parseFloat(0);}
		tabuladorSeis.main.nu_tedmf.setValue(parseFloat(tedm)+parseFloat(tedf));
	}
});

this.nu_tedmf = new Ext.form.NumberField({
	fieldLabel:'<b>6.3.3. Total empleos directos</b>',
	name:'nu_tedmf',
	value:this.OBJ.nu_tedmf,
	//allowBlank:false,
	width:100,
	minLength : 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 8},
	emptyText: '0',
	decimalPrecision: 0,
	allowNegative: false,
   	style: 'text-align: right',
	readOnly:true,
});

this.nu_tednf = new Ext.form.NumberField({
	fieldLabel:'6.3.4. Empleos directos nuevos Femeninos',
	name:'nu_tednf',
	value:this.OBJ.nu_tednf,
	//allowBlank:false,
	width:100,
	minLength : 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 7},
	validationEvent: 'blur',
	emptyText: '0',
	decimalPrecision: 0,
	allowNegative: false,
   	style: 'text-align: right',
	validator: function(value){
		tednf=value;
        	if(isNaN(tednf)){tednf = parseFloat(0);}
		tednm=tabuladorSeis.main.nu_tednm.getValue();
        	if(isNaN(tednm)){tednm = parseFloat(0);}
		tabuladorSeis.main.nu_tednmf.setValue(parseFloat(tednf)+parseFloat(tednm));
	}
});

this.nu_tednm = new Ext.form.NumberField({
	fieldLabel:'6.3.5. Empleos directos nuevos Masculinos',
	name:'nu_tednm',
	value:this.OBJ.nu_tednm,
	//allowBlank:false,
	width:100,
	minLength : 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 7},
	validationEvent: 'blur',
	emptyText: '0',
	decimalPrecision: 0,
	allowNegative: false,
   	style: 'text-align: right',
	validator: function(value){
		tednm=value;
        	if(isNaN(tednm)){tednm = parseFloat(0);}
		tednf=tabuladorSeis.main.nu_tednf.getValue();
        	if(isNaN(tednf)){tednf = parseFloat(0);}
		tabuladorSeis.main.nu_tednmf.setValue(parseFloat(tednm)+parseFloat(tednf));
	}
});

this.nu_tednmf = new Ext.form.NumberField({
	fieldLabel:'<b>6.3.6. Total empleos directos nuevos</b>',
	name:'nu_tednmf',
	value:this.OBJ.nu_tednmf,
	//allowBlank:false,
	width:100,
	minLength : 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 8},
	emptyText: '0',
	decimalPrecision: 0,
	allowNegative: false,
   	style: 'text-align: right',
	readOnly:true,
});

this.nu_tedsf = new Ext.form.NumberField({
	fieldLabel:'6.3.7. Empleos directos sostenidos Femeninos',
	name:'nu_tedsf',
	value:this.OBJ.nu_tedsf,
	//allowBlank:false,
	width:100,
	minLength : 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 7},
	validationEvent: 'blur',
	emptyText: '0',
	decimalPrecision: 0,
	allowNegative: false,
   	style: 'text-align: right',
	validator: function(value){
		tedsf=value;
        	if(isNaN(tedsf)){tedsf = parseFloat(0);}
		tedsm=tabuladorSeis.main.nu_tedsm.getValue();
        	if(isNaN(tedsm)){tedsm = parseFloat(0);}
		tabuladorSeis.main.nu_tedsmf.setValue(parseFloat(tedsf)+parseFloat(tedsm));
	}
});

this.nu_tedsm = new Ext.form.NumberField({
	fieldLabel:'6.3.8. Empleos directos sostenidos Masculinos',
	name:'nu_tedsm',
	value:this.OBJ.nu_tedsm,
	//allowBlank:false,
	width:100,
	minLength : 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 7},
	validationEvent: 'blur',
	emptyText: '0',
	decimalPrecision: 0,
	allowNegative: false,
   	style: 'text-align: right',
	validator: function(value){
		tedsm=value;
        	if(isNaN(tedsm)){tedsm = parseFloat(0);}
		tedsf=tabuladorSeis.main.nu_tedsf.getValue();
        	if(isNaN(tedsf)){tedsf = parseFloat(0);}
		tabuladorSeis.main.nu_tedsmf.setValue(parseFloat(tedsm)+parseFloat(tedsf));
	}
});

this.nu_tedsmf = new Ext.form.NumberField({
	fieldLabel:'<b>6.3.9. Total empleos directos sostenidos</b>',
	name:'nu_tedsmf',
	value:this.OBJ.nu_tedsmf,
	//allowBlank:false,
	width:100,
	minLength : 1,
	autoCreate: {tag: "input", type: "numeric", autocomplete: "off", maxlength: 8},
	emptyText: '0',
	decimalPrecision: 0,
	allowNegative: false,
   	style: 'text-align: right',
	readOnly:true,
});

this.fieldset4 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
		this.nu_tedf,
		this.nu_tedm,
		this.nu_tedmf,
		this.nu_tednf,
		this.nu_tednm,
		this.nu_tednmf,
		this.nu_tedsf,
		this.nu_tedsm,
		this.nu_tedsmf
		]
});

this.in_pvo = new Ext.form.ComboBox({
	fieldLabel:'6.4.3. ¿Este proyecto está vinculado a otro?',
	typeAhead: true,
	valueField: 'in_pvo',
	displayField:'in_pvo',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione...',
	selectOnFocus: true,
	mode: 'local',
	width:100,
	resizable:true,
	//allowBlank:false,
	name:'in_pvo',
	value:this.OBJ.in_pvo,
        store:	new Ext.data.SimpleStore({
        	fields : ['in_pvo'],
        	data : [['SI'],['NO']]
        })
});

this.in_pvo.on('select',function(cmb,record,index){
        if(tabuladorSeis.main.in_pvo.getValue()=='SI'){ 
		this.co_vinculo_proyecto.enable();           
        }else{
		this.co_vinculo_proyecto.disable();
		this.co_vinculo_proyecto.clearValue();
	}
},this);

this.co_vinculo_proyecto = new Ext.form.ComboBox({
	fieldLabel:'6.4.3.1. Si es si, especifique',
	store: this.storeCO_VINCULO_PROYECTO,
	typeAhead: true,
	valueField: 'co_vinculo_proyecto',
	displayField:'tx_vinculo_proyecto',
	hiddenName:'tx_vinculo_proyecto',
	//readOnly:(this.OBJ.co_vinculo_proyecto!='')?true:false,
	//style:(this.OBJ.co_vinculo_proyecto!='')?'background:#c9c9c9;':'',
	forceSelection:true,
	resizable:true,
	triggerAction: 'all',
	emptyText:'Seleccione...',
	selectOnFocus: true,
	mode: 'local',
	width:400,
	itemSelector: 'div.search-item',
	tpl: new Ext.XTemplate('<tpl for="."><div class="search-item"><div class="desc">{tx_vinculo_proyecto}</div></div></tpl>'),
	//listWidth:'400',
	resizable:true,
	//allowBlank:false
});

this.storeCO_VINCULO_PROYECTO.load();
	paqueteComunJS.funcion.seleccionarComboByCo({
	objCMB: this.co_vinculo_proyecto,
	value:  this.OBJ.co_vinculo_proyecto,
	objStore: this.storeCO_VINCULO_PROYECTO
});

this.co_vinculo_proyecto.disable();

if(tabuladorSeis.main.in_pvo.getValue()=='SI'){ 
	this.co_vinculo_proyecto.enable();           
}

this.tx_nipdv = new Ext.form.TextArea({
	fieldLabel:'6.4.3.2. Nombre Institución responsable del proyecto con el que se encuentra vinculado',
	name:'tx_nipdv',
	value:this.OBJ.tx_nipdv,
	width:400,
	maxLength: 150,
	//autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 150},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_nirpv = new Ext.form.TextArea({
	fieldLabel:'6.4.3.3. Nombre de la Instancia responsable del proyecto con el que se encuentra vinculado',
	name:'tx_nirpv',
	value:this.OBJ.tx_nirpv,
	width:400,
	maxLength: 300,
	//autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 300},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_nipcv = new Ext.form.TextArea({
	fieldLabel:'6.4.3.4. Nombre del proyecto con el que se encuentra vinculado',
	name:'tx_nipcv',
	value:this.OBJ.tx_nipcv,
	width:400,
	maxLength: 300,
	//autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 300},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.tx_eqmvp = new Ext.form.TextArea({
	fieldLabel:'6.4.3.5. ¿En qué medida se encuentran vinculados los proyectos',
	name:'tx_eqmvp',
	value:this.OBJ.tx_eqmvp,
	width:400,
	maxLength: 300,
	//autoCreate: {tag: "input", type: "text", autocomplete: "off", maxlength: 300},
	//allowBlank:false,
        listeners:{
            change: function(){
                this.setValue(String(this.getValue()).toUpperCase());
            }
        }
});

this.fieldset5 = new Ext.form.FieldSet({
	autoWidth:true,
        items:[
		this.in_pvo,
		this.co_vinculo_proyecto,
		this.tx_nipdv,
		this.tx_nirpv,
		this.tx_nipcv,
		this.tx_eqmvp
		]
});

this.panelDatos62 = new Ext.Panel({
    title: '6.2. BENEFICIARIOS ESTIMADOS',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
	this.fieldset3
	]
});

this.panelDatos63 = new Ext.Panel({
    title: '6.3. EMPLEOS ESTIMADOS',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
	this.fieldset4	
	]
});

this.panelDatos64 = new Ext.Panel({
    title: '6.4. RELACIÓN INTERINSTITUCIONAL, INTRAINSTITUCIONAL Y EL PODER POPULAR',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[
	this.fieldset5
	]
});

this.panelDatos = new Ext.TabPanel({
    activeTab:0,
    enableTabScroll:true,
    deferredRender: false,
    title: '6. ALCANCE E IMPACTO DEL PROYECTO',
    bodyStyle:'padding:5px;',
    autoHeight:true,
    items:[this.panelDatos61,this.panelDatos62,this.panelDatos63,this.panelDatos64]
});
},
getStoreCO_VINCULO_PROYECTO:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
        root:'data',
	baseParams: {
		op: 30
	},
        fields:[
            {name: 'co_vinculo_proyecto'},{name: 'tx_vinculo_proyecto'}
            ]
    });
    return this.store;
},
getStoreCO_UNIDADES_MEDIDA:function(){
    this.store = new Ext.data.JsonStore({
        url:'formulacion/modulos/seguimiento_proyecto/funcion.php',
        root:'data',
	baseParams: {
		op: 29
	},
        fields:[
            {name: 'co_unidades_medida'},{name: 'tx_unidades_medida'}
            ]
    });
    return this.store;
}
};
Ext.onReady(tabuladorSeis.main.init, tabuladorSeis.main);
</script>
