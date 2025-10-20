<?php        
session_start(); 
if($_SESSION['estatus']!='OK'){
	header('Location: ../../');
}   
include("../../configuracion/ConexionComun.php");

$codigo = decode($_POST['codigo']);
$data = json_encode(array(
	"co_proyecto_acc_espec" => $codigo,
));
?>
<script type="text/javascript">
Ext.ns("partidaLista");
partidaLista.main = {
color: function(valor){
        if(valor > 0){
            return '<span style="color:green;">' + valor + '</span>';
        }else if(valor < 0){
            return '<span style="color:red;">' + valor + '</span>';
        }
        return valor;
},
condicion:function(codigo){
    return (codigo=='0')?'NO':'SI';
},
init:function(){

this.OBJ = paqueteComunJS.funcion.doJSON({stringData:'<?php echo $data ?>'});

//<ClavePrimaria>
this.co_proyecto_acc_espec = new Ext.form.Hidden({
    name:'co_proyecto_acc_espec',
    value:this.OBJ.co_proyecto_acc_espec});
//</ClavePrimaria>

//Mascara general del modulo
this.mascara = new Ext.LoadMask(Ext.getBody(), {msg:"Cargando..."});

//objeto store
this.store_lista = this.getLista();

Ext.ux.grid.GroupSummary.Calculations['nuMonto'] = function(v, record, field){
	v+=parseFloat(record.data.nu_monto);
	return v;
};
this.summary = new Ext.ux.grid.GroupSummary();

//Grid principal
this.gridPanel_ = new Ext.grid.GridPanel({
    border:false,
    store: this.store_lista,
    loadMask:true,
//    frame:true,
    autoHeight:true,
    columns: [
    new Ext.grid.RowNumberer(),
    {header: 'co_partida_acc_espec',hidden:true, menuDisabled:true,dataIndex: 'co_partida_acc_espec'},
    {header: 'PARTIDA', width:80,  menuDisabled:true, sortable: true,  dataIndex: 'tx_partida'},
    {header: 'DENOMINACIÓN', width:350,  menuDisabled:true, sortable: true, summaryRenderer: function(v, params, data){return '<b>TOTALES</b>';}, dataIndex: 'tx_denominacion'},
    {header: 'Monto (BS)', width:130,  menuDisabled:true, sortable: true, dataIndex: 'nu_monto',summaryType: 'nuMonto', renderer: formatoNumero},
    {header: 'Partida',summaryType: 'sum',summaryRenderer: function(v, params, data){return 'Total';},autoWidth: true, sortable: true,groupable: false,  dataIndex: 'tx_partida_madre'},
    ],
    view: new Ext.grid.GroupingView({
        groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Partidas" : "Partida"]})',
        forceFit: true,
        showGroupName: true,
        enableNoGroups: false,
	enableGroupingMenu: false,
        hideGroupedColumn: true
    }),
    stripeRows: true,
    autoScroll:true,
    stateful: true,
    plugins: this.summary,
    bbar: new Ext.PagingToolbar({
        pageSize: 100,
        store: this.store_lista,
        displayInfo: true,
        displayMsg: '<span style="color:black">Registros: {0} - {1} de {2}</span>',
        emptyMsg: "<span style=\"color:black\">No se encontraron registros</span>"
    })
});

this.gridPanel_.render("contenedorpartidaLista<?php echo $codigo;?>");

//Cargar el grid
this.store_lista.baseParams.paginar = 'si';
this.store_lista.baseParams.co_proyecto_acc_espec = this.OBJ.co_proyecto_acc_espec;
this.store_lista.baseParams.op = 1;
this.store_lista.load();
this.store_lista.on('beforeload',function(){
panel_detalle.collapse();
});
},
getLista: function(){
this.Store = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
            url:'formulacion/modulos/seguimiento_proyecto_ae/funcion.php',
            method: 'POST'
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'total'
        },
        [
	    {name: 'co_partida_acc_espec'},
	    {name: 'tx_partida'},
	    {name: 'tx_denominacion'},
	    {name: 'nu_monto'},
	    {name: 'tx_partida_madre'},
        ]),
        sortInfo:{
            field: 'tx_partida',
            direction: "ASC"
        },
        groupField:'tx_partida_madre'

});
return this.Store;
}
};
Ext.onReady(partidaLista.main.init, partidaLista.main);
</script>
<div id="contenedorpartidaLista<?php echo $codigo;?>"></div>
