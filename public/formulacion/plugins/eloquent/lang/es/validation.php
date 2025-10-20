<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    "accepted"             => "El campo :attribute debe ser aceptado.",
    "active_url"           => "El campo :attribute no es una URL válida.",
    "after"                => "El campo :attribute debe ser una fecha posterior a :date.",
    "alpha"                => "El campo :attribute sólo puede contener letras.",
    "alpha_dash"           => "El campo :attribute sólo puede contener letras, números y guiones (a-z, 0-9, -_).",
    "alpha_num"            => "El campo :attribute sólo puede contener letras y números.",
    "array"                => "El campo :attribute debe ser un array.",
    "before"               => "El campo :attribute debe ser una fecha anterior a :date.",
    "between"              => [
        "numeric" => "El campo :attribute debe ser un valor entre :min y :max.",
        "file"    => "El archivo :attribute debe pesar entre :min y :max kilobytes.",
        "string"  => "El campo :attribute debe contener entre :min y :max caracteres.",
        "array"   => "El campo :attribute debe contener entre :min y :max elementos.",
    ],
    "boolean"              => "El campo :attribute debe ser verdadero o falso.",
    "confirmed"            => "El campo confirmación de :attribute no coincide.",
    "date"                 => "El campo :attribute no corresponde con una fecha válida.",
    "date_format"          => "El campo :attribute no corresponde con el formato de fecha :format.",
    "different"            => "Los campos :attribute y :other han de ser diferentes.",
    "digits"               => "El campo :attribute debe ser un número de :digits dígitos.",
    "digits_between"       => "El campo :attribute debe contener entre :min y :max dígitos.",
    "email"                => "El campo :attribute no corresponde con una dirección de e-mail válida.",
    "filled"               => "El campo :attribute es obligatorio.",
    "exists"               => "El campo :attribute no existe.",
    "image"                => "El archivo :attribute debe ser una imagen.",
    "in"                   => "El campo :attribute debe ser igual a alguno de estos valores :values",
    "integer"              => "El campo :attribute debe ser un número entero.",
    "ip"                   => "El campo :attribute debe ser una dirección IP válida.",
    "max"                  => [
        "numeric" => "El campo :attribute debe ser menor que :max.",
        "file"    => "El archivo :attribute debe pesar menos que :max kilobytes.",
        "string"  => "El campo :attribute debe contener menos de :max caracteres.",
        "array"   => "El campo :attribute debe contener al menos :max elementos.",
    ],
    "mimes"                => "El campo :attribute debe ser un archivo de tipo :values.",
    "min"                  => [
        "numeric" => "El campo :attribute debe tener al menos :min.",
        "file"    => "El archivo :attribute debe pesar al menos :min kilobytes.",
        "string"  => "El campo :attribute debe contener al menos :min caracteres.",
        "array"   => "El campo :attribute no debe contener más de :min elementos.",
    ],
    "not_in"               => "El campo :attribute seleccionado es invalido.",
    "numeric"              => "El campo :attribute debe ser un numero.",
    "regex"                => "El formato del campo :attribute es inválido.",
    "required"             => "El campo :attribute es obligatorio.",
    "required_if"          => "El campo :attribute es obligatorio cuando el campo :other es :value.",
    "required_with"        => "El campo :attribute es obligatorio cuando :values está presente.",
    "required_with_all"    => "El campo :attribute es obligatorio cuando :values está presente.",
    "required_without"     => "El campo :attribute es obligatorio cuando :values no está presente.",
    "required_without_all" => "El campo :attribute es obligatorio cuando ningún campo :values están presentes.",
    "same"                 => "Los campos :attribute y :other deben coincidir.",
    "size"                 => [
        "numeric" => "El campo :attribute debe ser :size.",
        "file"    => "El archivo :attribute debe pesar :size kilobytes.",
        "string"  => "El campo :attribute debe contener :size caracteres.",
        "array"   => "El campo :attribute debe contener :size elementos.",
    ],
    "unique"               => "El elemento :attribute ya está en uso.",
    "url"                  => "El formato de :attribute no corresponde con el de una URL válida.",
    "timezone"             => "El campo :attribute debe contener una zona válida.",

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'documenton' => [
            'in' => 'El campo :attribute debe ser igual a alguno de estos valores V,E,P.',
        ],
        'documentoj' => [
            'in' => 'El campo :attribute debe ser igual a alguno de estos valores J,G.',
        ],
        'tipo_documento' => [
            'in' => 'El campo :attribute debe ser igual a alguno de estos valores V,E,J,G.',
        ],
        'rif' => [
            'regex' => 'El formato del campo :attribute es inválido, debe ser 00000000-0, (Si el RIF es menor a nueve (9) dígitos complete con ceros (0) a la izquierda).',
        ],
        'cuenta' => [
            'regex' => 'El formato del campo :attribute es inválido, debe ser 0000-0000-00-0000000000.',
        ],
        'telefono_movil' => [
            'regex' => 'El formato del campo :attribute es inválido, debe ser 0000-0000000.',
        ],
        'telefono_fijo' => [
            'regex' => 'El formato del campo :attribute es inválido, debe ser 0000-0000000.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
	'documenton' => 'Tipo de Documento',
	'documentoj' => 'Tipo de Documento',
	'tipo_documento' => 'Tipo de Documento',
	'cedula' => 'Cedula de Identidad',
	'rif' => 'RIF',
	'cuenta' => 'Cuenta Bancaria',
	'usuario' => 'nombre de usuario',
	'contraseña_confirmation' => 'repetir contraseña',
	'email' => 'correo electronico',
	'email_confirmation' => 'repetir correo electronico',
	'codigoseg' => 'Codigo de Seguridad',
	'proyecto_proyecto' => '1.0. CÓDIGO DEL PROYECTO',
	'ejecutor_proyecto' => '1.2. UNIDAD EJECUTORA RESPONSABLE',
	'nombre_proyecto' => '1.3. NOMBRE DEL PROYECTO',
	'fecha_ini_proyecto' => '1.4. FECHA DE INICIO',
	'fecha_fin_proyecto' => '1.5. FECHA DE CULMINACIÓN',
	'status_proyecto' => '1.6. ESTATUS DEL PROYECTO',
	'objetivo_proyecto' => '1.7. OBJETIVO GENERAL DEL PROYECTO',
	'sit_presupuesto_proyecto' => '1.8. SITUACIÓN PRESUPUESTARIA',
	'monto_proyecto' => '1.9. MONTO TOTAL PROYECTO BS.',
	'descripcion_proyecto' => '1.10. DESCRIPCIÓN DEL PROYECTO',
	'clase_sector_proyecto' => '1.11.1. SECTOR',
	'clase_subsector_proyecto' => '1.11.2. SUB-SECTOR',
	'plan_operativo_proyecto' => '1.12. ¿A CUÁL PLAN OPERATIVO CONSIDERA QUE PERTENECE EL PROYECTO?',
	'obj_historico_vinculo' => '2.1. OBJETIVO HISTÓRICO',
	'obj_nacional_vinculo' => '2.2. OBJETIVO NACIONAL',
	'ob_estrategico_vinculo' => '2.3. OBJETIVO ESTRATÉGICO',
	'obj_general_vinculo' => '2.4. OBJETIVO GENERAL',
	'area_estrategica_vinculo' => '2.5.1. AREA ESTRATEGICA',
	'ambito_estado_vinculo' => '2.5.2. AMBITO',
	'objetivo_estado_vinculo' => '2.5.3. OBJETIVO ESTRATEGICO',
	'macroproblema_vinculo' => '2.5.4. MACRO PROBLEMA',
	'nodo_vinculo' => '2.5.5. NUDOS CRITICOS',
	'ambito_localizacion' => '3.1. ÁMBITO',
	'responsable_nombres' => "5.1. NOMBRE",
	'reponsable_cedula' => "5.1.1. CÉDULA DE IDENTIDAD",
	'responsable_correo' => "5.1.2. CORREO ELECTRÓNICO",
	'responsable_telefono' => "5.1.3. NÚMERO TELEFÓNICO",
	'tecnico_nombres' => "5.2. NOMBRE",
	'tecnico_cedula' => "5.2.1. CÉDULA DE IDENTIDAD",
	'tecnico_correo' => "5.2.2. CORREO ELECTRÓNICO",
	'tecnico_telefono' => "5.2.3. NÚMERO TELEFÓNICO",
	'tecnico_unidad' => "5.2.4. UNIDAD TÉCNICA",
	'registrador_nombres' => "5.3. NOMBRE",
	'registrador_cedula' => "5.3.1. CÉDULA DE IDENTIDAD",
	'registrador_correo' => "5.3.2. CORREO ELECTRÓNICO",
	'registrador_telefono' => "5.3.3. NÚMERO TELEFÓNICO",
	'administrador_nombres' => "5.4. NOMBRE",
	'administrador_cedula' => "5.4.1. CÉDULA DE IDENTIDAD",
	'administrador_correo' => "5.4.2. CORREO ELECTRÓNICO",
	'administrador_telefono' => "5.4.3. NÚMERO TELEFÓNICO",
	'administrador_unidad' => "5.4.4. UNIDAD ADMINISTRADORA",
	/*Acciones centralizadas*/
	'accion_ac' => '1.2. TIPO DE ACCIÓN',
	'descripcion_ac' => '1.3. DESCRIPCIÓN',
	'ejecutor_ac' => '1.4. UNIDAD EJECUTORA RESPONSABLE',
	'mision_ac' => '1.4.1. MISION',
	'vision_ac' => '1.4.2. VISION',
	'objetivo_ac' => '1.4.3. OBJETIVOS DE LA INSTITUCION',
	'sector_ac' => '1.5.1. SECTOR',
	'subsector_ac' => '1.5.2. SUB-SECTOR',
	'fecha_ini_ac' => '1.6. FECHA DE INICIO',
	'fecha_fin_ac' => '1.7. FECHA DE CULMINACIÓN',
	'sit_presupuesto_ac' => '1.8. SITUACIÓN PRESUPUESTARIA',
	'monto_ac' => '1.9. MONTO TOTAL (BS.)',
	'poblacion_ac' => '1.9.1. POBLACIÓN A BENEFICIAR',
	'empleo_ac' => '1.9.2. EMPLEOS PREVISTOS',
	'producto_ac' => '1.9.3. PRODUCTO PROGRAMADO DEL OBJETIVO',
	'resultado_ac' => '1.9.4. RESULTADOS PROGRAMADOS',
    ],

];
