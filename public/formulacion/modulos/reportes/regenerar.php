<?php
require_once '../../comun.php';

use Reingsys as re;

$op = intval( $_REQUEST['op'] );

if ( $usuario->co_rol != 1 ) {
    die();
}

try {
    switch ( $op ) {
        case 1: //acs
            $paraTransaccion->StartTrans();
            $sql2 = 'update mapa_acs set edo_reg = false;';
            /*$sql = <<<EOT
insert into mapa_acs(
  id_ac, ef, se, ss, ac, ae, pro, sub, act, ej_ac, ej_ae, edo_reg
) select t46.id, t25.co_ejercicio_fiscal as ejercicio,
    t18a.co_sector as sector,
    '' as ss,
    t46.id_accion,
    t53.numero,
    ((t46.id_accion + 50))::character(2) AS proyecto,
    '00'::character(2) AS subproyecto,
    lpad(rank() OVER (PARTITION BY ROW(t25.co_ejercicio_fiscal, t18a.co_sector, t52.id) ORDER BY ROW(t25.co_ejercicio_fiscal, t18a.co_sector, t52.id, t46.id_ejecutor, t47.id_ejecutor, t53.numero))::text, 2, '0'::text) AS actividad,
    t46.id_ejecutor,
    t47.id_ejecutor,
    true
from t25_ejercicio_fiscal as t25
    join t46_acciones_centralizadas as t46 on t46.id_ejercicio = t25.co_ejercicio_fiscal
    join t52_ac_predefinidas as t52 on t52.id = t46.id_accion
    join t24_ejecutores as t24 on t24.id_ejecutor = t46.id_ejecutor
    join t18_sectores as t18 on t18.co_sectores = t46.id_subsector
    join t18_sectores as t18a on t18.co_sector = t18a.co_sector
        and (t18a.co_sub_sector is null or t18a.co_sub_sector = '')
    join t47_ac_accion_especifica as t47 on t46.id = t47.id_accion_centralizada
    join t24_ejecutores as t24a on t24a.id_ejecutor = t47.id_ejecutor
    join t53_ac_ae_predefinidas as t53 on t53.id = t47.id_accion
        and t53.padre = t52.id
where t25.edo_reg
    and t46.edo_reg
    and t47.edo_reg
    and t24.edo_reg
    and t24a.edo_reg
    and t18.edo_reg
    and t18a.edo_reg
order by ejercicio
    , t24.id_ejecutor::integer
    , t18a.co_sector::integer
    , t46.id_accion
    , t53.numero;
EOT;*/

            /*$sql = <<<EOT
insert into mapa_acs(
  id_ac, ef, se, ss, ac, ae, pro, sub, act, ej_ac, ej_ae, edo_reg
) select t46.id, t25.id as ejercicio,
    t18a.co_sector as sector,
    '' as ss,
    t46.id_accion,
    t53.numero,
    ((t46.id_accion + 50))::character(2) AS proyecto,
    '00'::character(2) AS subproyecto,
    lpad(rank() OVER (PARTITION BY ROW(t25.id, t18a.co_sector, t52.id) ORDER BY ROW(t25.id, t18a.co_sector, t52.id, t46.id_ejecutor, t47.id_ejecutor, t53.numero))::text, 2, '0'::text) AS actividad,
    t46.id_ejecutor,
    t47.id_ejecutor,
    true
from mantenimiento.tab_ejercicio_fiscal as t25
    join t46_acciones_centralizadas as t46 on t46.id_ejercicio = t25.id
    join t52_ac_predefinidas as t52 on t52.id = t46.id_accion
    join mantenimiento.tab_ejecutores as t24 on t24.id_ejecutor = t46.id_ejecutor
    join t18_sectores as t18 on t18.co_sectores = t46.id_subsector
    join t18_sectores as t18a on t18.co_sector = t18a.co_sector
        and (t18a.co_sub_sector is null or t18a.co_sub_sector = '')
    join t47_ac_accion_especifica as t47 on t46.id = t47.id_accion_centralizada
    join mantenimiento.tab_ejecutores as t24a on t24a.id_ejecutor = t47.id_ejecutor
    join t53_ac_ae_predefinidas as t53 on t53.id = t47.id_accion
        and t53.padre = t52.id
where --t25.in_activo and
    t46.edo_reg
    and t47.edo_reg
    and t24.in_activo
    and t24a.in_activo
    and t18.edo_reg
    and t18a.edo_reg
order by ejercicio
    , t24.id_ejecutor::integer
    , t18a.co_sector::integer
    , t46.id_accion
    , t53.numero;
EOT;*/

            $sql = <<<EOT
insert into mapa_acs(
  id_ac, ef, se, ss, ac, ae, pro, sub, act, ej_ac, ej_ae, edo_reg
) select t46.id, t25.id as ejercicio,
t18a.co_sector as sector,
'' as ss,
t46.id_accion,
t53.nu_numero,
((t46.id_accion + 50))::character(2) AS proyecto,
'00'::character(2) AS subproyecto,
lpad(rank() OVER (PARTITION BY ROW(t25.id, t18a.co_sector, t52.id) ORDER BY ROW(t25.id, t18a.co_sector, t52.id, t46.id_ejecutor, t47.id_ejecutor, t53.nu_numero))::text, 2, '0'::text) AS actividad,
t46.id_ejecutor,
t47.id_ejecutor,
true
from mantenimiento.tab_ejercicio_fiscal as t25
join t46_acciones_centralizadas as t46 on t46.id_ejercicio = t25.id
join mantenimiento.tab_ac_predefinida as t52 on t52.id = t46.id_accion
join mantenimiento.tab_ejecutores as t24 on t24.id_ejecutor = t46.id_ejecutor
join mantenimiento.tab_sectores as t18a on t46.id_subsector=t18a.id
join mantenimiento.tab_sectores as t18b on t18a.co_sector = t18b.co_sector and t18b.nu_nivel = 1                    
join t47_ac_accion_especifica as t47 on t46.id = t47.id_accion_centralizada
join mantenimiento.tab_ejecutores as t24a on t24a.id_ejecutor = t47.id_ejecutor
join mantenimiento.tab_ac_ae_predefinida as t53 on t53.id = t47.id_accion
    and t53.id_padre = t52.id
where --t25.in_activo and
t46.edo_reg
and t47.edo_reg
and t24.in_activo
and t24a.in_activo
and t18a.in_activo
order by ejercicio
, t24.id_ejecutor::integer
, t18a.co_sector::integer
, t46.id_accion
, t53.nu_numero;
EOT;
            $res = $comunes->EjecutarQuery( $sql2 );
            $res = $comunes->EjecutarQuery( $sql );
            $paraTransaccion->CompleteTrans();
            $respuesta = re\Helpers::responder( true );
            break;

        case 2: //proyectos
            $paraTransaccion->StartTrans();
            $sql2 = 'update mapa_proyectos set edo_reg = false;';
            /*$sql = <<<EOT
insert into mapa_proyectos(
  id_ejercicio,
  id_ejecutor,
  id_sector,
  id_proyecto,
  id_ae,
  nombre_proyecto,
  nombre_ae,
  nue_proyecto,
  nue_subproyecto,
  nue_actividad,
  edo_reg
)
with pr as (
select t25.co_ejercicio_fiscal as ejercicio,
	t26.clase_sector as sector,
	t24.id_ejecutor as ejecutor,
	rank() over (
		partition by ( t25.co_ejercicio_fiscal, t26.clase_sector )
		order by t25.co_ejercicio_fiscal, t26.clase_sector, t24.id_ejecutor, t26.co_proyectos
	) as proyecto,
	'00'::character(2) as subproyecto,
	t26.co_proyectos,
	t26.id_proyecto,
	t26.nombre as pr_nom,
	t24.tx_ejecutor as ej_pr_nom
from t25_ejercicio_fiscal as t25
	join t26_proyectos as t26 on t26.id_ejercicio::integer = t25.co_ejercicio_fiscal
	join t24_ejecutores as t24 on t24.id_ejecutor = t26.id_ejecutor
	join t18_sectores as t18 on t18.co_sector = t26.clase_sector
		and t18.co_sub_sector = t26.clase_subsector
where t25.edo_reg
	and t26.edo_reg
	and t24.edo_reg
	and t18.edo_reg
) select ejercicio, ejecutor, sector, pr.id_proyecto, t39.tx_codigo,
	pr.pr_nom, t39.descripcion as ae_nom,
	lpad( proyecto::text, 2, '0' ) as proyecto
	, subproyecto,
	lpad( ( rank() over (
		partition by ( ejercicio, sector, ejecutor, proyecto )
		order by ( ejercicio, sector, ejecutor, proyecto, t24.id_ejecutor::int, t39.co_proyecto_acc_espec )
	) )::text, 2, '0' ) as actividad,
	true
from pr
	join t39_proyecto_acc_espec as t39 on t39.id_proyecto = pr.id_proyecto
	join t24_ejecutores as t24 on t24.co_ejecutores = t39.co_ejecutores
where t39.edo_reg
	and t24.edo_reg
order by ejercicio
	, sector::integer
	, proyecto::integer
	, actividad;
EOT;*/

            $sql = <<<EOT
insert into mapa_proyectos(
  id_ejercicio,
  id_ejecutor,
  id_sector,
  id_proyecto,
  id_ae,
  nombre_proyecto,
  nombre_ae,
  nue_proyecto,
  nue_subproyecto,
  nue_actividad,
  edo_reg
)
with pr as (
select t25.id as ejercicio,
	t26.clase_sector as sector,
	t24.id_ejecutor as ejecutor,
	rank() over (
		partition by ( t25.id, t26.clase_sector )
		order by t25.id, t26.clase_sector, t24.id_ejecutor, t26.co_proyectos
	) as proyecto,
	'00'::character(2) as subproyecto,
	t26.co_proyectos,
	t26.id_proyecto,
	t26.nombre as pr_nom,
	t24.tx_ejecutor as ej_pr_nom
from mantenimiento.tab_ejercicio_fiscal as t25
	join t26_proyectos as t26 on t26.id_ejercicio::integer = t25.id
	join mantenimiento.tab_ejecutores as t24 on t24.id_ejecutor = t26.id_ejecutor
	join t18_sectores as t18 on t18.co_sector = t26.clase_sector
		and t18.co_sub_sector = t26.clase_subsector
where --t25.in_activo and
	t26.edo_reg
	and t24.in_activo
	and t18.edo_reg
) select ejercicio, ejecutor, sector, pr.id_proyecto, t39.tx_codigo,
	pr.pr_nom, t39.descripcion as ae_nom,
	lpad( proyecto::text, 2, '0' ) as proyecto
	, subproyecto,
	lpad( ( rank() over (
		partition by ( ejercicio, sector, ejecutor, proyecto )
		order by ( ejercicio, sector, ejecutor, proyecto, t24.id_ejecutor::int, t39.co_proyecto_acc_espec )
	) )::text, 2, '0' ) as actividad,
	true
from pr
	join t39_proyecto_acc_espec as t39 on t39.id_proyecto = pr.id_proyecto
	join mantenimiento.tab_ejecutores as t24 on t24.id = t39.co_ejecutores
where t39.edo_reg
	and t24.in_activo
order by ejercicio
	, sector::integer
	, proyecto::integer
	, actividad;
EOT;

            $res = $comunes->EjecutarQuery( $sql2 );
            $res = $comunes->EjecutarQuery( $sql );
            $paraTransaccion->CompleteTrans();
            $respuesta = re\Helpers::responder( true );
            break;
    };
} catch ( \ADODB_Exception $e ) {
    error_log( json_encode( re\Helpers::jTraceEx( $e ) ) );
    //FIXME feo
    $mensaje = 'ocurrió una falla trabajando con la base de datos';
    if ( $paraTransaccion->HasFailedTrans() ) {
        $paraTransaccion->CompleteTrans();
    }
    $ms = array();
    if ( preg_match( '/ERROR\:\ *(.*)\s*CONTEXT\:/', $e->getMessage(), $ms ) === 1 ) {
        $mensaje = $ms[1];
    }
    $respuesta = re\Helpers::responder( false,
        'Error en Transacción: ' . $mensaje
    );
} catch( \Exception $e ) {
    error_log( json_encode( re\Helpers::jTraceEx( $e ) ) );
    $respuesta = re\Helpers::responder( false,
        'Error procesando la solicitud'
    );
}

echo $respuesta;

