<?php
include("configuracion/ConexionComun.php");

function  ArmaMenu($co_rol){
        /*
         * Se buscan las opciones de menu padre
         */
	$comunes = new ConexionComun();

	/*$sql= "select t03.* from t03_menu as t03 inner join t04_rolmenu as t04 on t03.co_menu=t04.co_menu
	where t04.co_rol = '$co_rol' and t03.co_padre=0 and t04.in_ver='t' order by nu_orden asc;";*/
	$sql= "select t03.id as co_menu, t03.de_menu, t03.de_icono
	from autenticacion.tab_menu as t03 
	inner join autenticacion.tab_rol_menu as t04 on t03.id = t04.id_tab_menu
	where t04.id_tab_rol = '$co_rol' and t03.id_padre = 0 and t04.in_estatus='t' order by nu_orden asc;";
	$resultado = $comunes->ObtenerFilasBySqlSelect($sql);			

        $menu = '';
        foreach($resultado as $key => $fila){

                $cantidad = cantidad_hijos($fila['co_menu'],$co_rol);

                if($cantidad > 0)
                {
                       $menu.= "{
                                 title:'<b>".utf8_encode($fila['de_menu'])."</b>',
                                 autoScroll:true,
				 border:false,
				 collapsed:true,
				 iconCls:'".utf8_encode($fila['de_icono'])."',
				 autoHeight:true,
				 items:[    new Ext.tree.TreePanel({
					    id:'".$fila['co_menu']."',
					    loader: new Ext.tree.TreeLoader(),
					    rootVisible:false,
					    lines:true,
					    autoScroll:true,
					    border: false,
					    autoHeight:true,
					    listeners: {
					    click : {
					    scope : this,
					    fn    : function( n, e ) 
					    {
						if(n.leaf){
						    //Accedemos a los a atributod del json que usamos para crear el nodo con
						    myobject = n;
						    if (n.attributes.url){url = n.attributes.url;} else {url =n.id;}
						    //Abrimos el nuevo tab
						    addTab(n.id,n.text,url,n.attributes.tabType,n.attributes.iconCls);
						}
					    }
					    }
					    },
					    root: new Ext.tree.AsyncTreeNode({
						children:[".ArmaSubmenu($fila['co_menu'],$co_rol)."]
					    })
				    })]
                                },";
                }
        }
        return $menu;
    }

function cantidad_hijos($co_padre,$co_rol){

	$comunes = new ConexionComun();

	/*$sql= "select count(*) as c from t03_menu as t03 inner join t04_rolmenu as t04 on t03.co_menu=t04.co_menu
	where t04.co_rol = '$co_rol' and t03.co_padre='$co_padre' and t04.in_ver='t';";*/
	$sql= "SELECT autenticacion.sp_catidad_menu_hijo('$co_padre', '$co_rol');";
	$cantidad = $comunes->ObtenerFilasBySqlSelect($sql);

        return intval($cantidad[0]['sp_catidad_menu_hijo']);
    }

function cantidad_hijosPrivilegio($co_padre,$co_rol){

	$comunes = new ConexionComun();

	/*$sql= "select count(*) as c from t03_menu as t03 inner join t04_rolmenu as t04 on t03.co_menu=t04.co_menu
	where t04.co_rol = '$co_rol' and t03.co_padre='$co_padre';";*/
	$sql= "SELECT autenticacion.sp_catidad_menu_privilegio('$co_padre', '$co_rol');";
	$cantidad = $comunes->ObtenerFilasBySqlSelect($sql);

        return intval($cantidad[0]['sp_catidad_menu_privilegio']);
    }

function ArmaSubmenu($co_padre,$co_rol){

	$comunes = new ConexionComun();

	/*$sql= "select t03.* from t03_menu as t03 inner join t04_rolmenu as t04 on t03.co_menu=t04.co_menu
	where t04.co_rol = '$co_rol' and t03.co_padre='$co_padre' and t04.in_ver='t' order by nu_orden asc;";*/
	$sql= "select t03.id as co_menu, t03.de_menu, t03.de_icono, t03.da_url
	from autenticacion.tab_menu as t03 
	inner join autenticacion.tab_rol_menu as t04 on t03.id = t04.id_tab_menu
	where t04.id_tab_rol = '$co_rol' and t03.id_padre = '$co_padre' and t04.in_estatus='t' order by nu_orden asc;";
	$resultado = $comunes->ObtenerFilasBySqlSelect($sql);			

        $submenu = '';

        foreach($resultado as $key => $fila){

            $cantidad = cantidad_hijosPrivilegio($fila['co_menu'],$co_rol);

            if($cantidad > 0)
            {
                 $cantidad_hijos = cantidad_hijos($fila['co_menu'],$co_rol);

                 if($cantidad_hijos > 0){
                       $submenu.= "{
                                 text:'".utf8_encode($fila['de_menu'])."',
                                 children:[".ArmaSubmenu($fila['co_menu'],$co_rol)."]
                                },";
                 }
            }else{

			$submenu.= "{
				id: '".$fila['co_menu']."',
				url: '".$fila['da_url']."',
				tabType:'load',
				text:'".utf8_encode($fila['de_menu'])."',
				iconCls:'".$fila['de_icono']."',
				leaf:true
			},";
            }
        }

        return  $submenu;
    }

function  ArmaMenuPrivilegio($co_rol){

	$comunes = new ConexionComun();

        /*
         * Se buscan las opciones de menu padre
         */
	/*$sql= "select t03.co_menu,t03.tx_menu,t03.tx_icono,t04.co_rolmenu,t04.in_ver from t03_menu as t03 inner join t04_rolmenu as t04 on t03.co_menu=t04.co_menu
	where t04.co_rol = '$co_rol' and t03.co_padre=0 order by nu_orden asc;";*/
	$sql= "select t03.id as co_menu, t03.de_menu, t03.de_icono, t04.id as co_rolmenu, t04.in_estatus 
	from autenticacion.tab_menu as t03 
	inner join autenticacion.tab_rol_menu as t04 on t03.id = t04.id_tab_menu
	where t04.id_tab_rol = '$co_rol' and t03.id_padre=0 order by nu_orden asc;";
	$resultado = $comunes->ObtenerFilasBySqlSelect($sql);

        $menu = '';
        foreach($resultado as $key => $fila){

                $cantidad = cantidad_hijosPrivilegio($fila['co_menu'],$co_rol);
                if($cantidad > 0)
                {
                       $menu.= "{
                                 text:'".utf8_encode($fila['de_menu'])."',
				 cls:'forum-ct',
				 iconCls:'forum-parent',
                                // expanded:true,
                                 children:[".ArmaSubmenuPrivilegio($fila['co_menu'],$co_rol)."]
                                },";
                }
        }
        return $menu;
    }

function ArmaSubmenuPrivilegio($co_padre,$co_rol){

	$comunes = new ConexionComun();

	/*$sql= "select t03.co_menu,t03.tx_menu,t03.tx_icono,t04.co_rolmenu,t04.in_ver from t03_menu as t03 inner join t04_rolmenu as t04 on t03.co_menu=t04.co_menu
	where t04.co_rol = '$co_rol' and t03.co_padre='$co_padre' order by nu_orden asc;";*/
	$sql= "select t03.id as co_menu, t03.de_menu, t03.de_icono, t04.id as co_rolmenu, t04.in_estatus 
	from autenticacion.tab_menu as t03
	inner join autenticacion.tab_rol_menu as t04 on t03.id = t04.id_tab_menu
	where t04.id_tab_rol = '$co_rol' and t03.id_padre ='$co_padre' order by nu_orden asc;";
	$resultado = $comunes->ObtenerFilasBySqlSelect($sql);

        $submenu = '';

        foreach($resultado as $key => $fila){
            $cantidad = cantidad_hijosPrivilegio($fila['co_menu'],$co_rol);
            if($cantidad > 0)
            {
                       $submenu.= "{
                                 text:'".utf8_encode($fila['de_menu'])."',
                                 id:'".$fila['co_rolmenu']."',
                                 children:[".ArmaSubmenuPrivilegio($fila['co_menu'],$co_rol)."]
                                 },";
            }else{
                $submenu.= "{
                                    text:'".utf8_encode($fila['de_menu'])."',
                                    id:'".$fila['co_rolmenu']."',
                                    iconCls:'".utf8_encode($fila['de_icono'])."',
                                    leaf:true, ";
                if($fila['in_estatus']=='t')
                 $submenu.= "       checked: true },";
                else
                 $submenu.= "       checked: false },";
            }
        }
        return  $submenu;
    }
?>
