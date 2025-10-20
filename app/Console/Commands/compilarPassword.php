<?php

namespace matriz\Console\Commands;
//*******agregar esta linea******//
use matriz\Models\Autenticacion\tab_usuarios;
use DB;
use Auth;
use Crypt;
use Hash;
//*******************************//
use Illuminate\Console\Command;

class compilarPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matriz:compilarPassword';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para hacer Hash a todas las contraseñas.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      DB::beginTransaction();
      try {

        $listado_usuario = tab_usuarios::select( 'id', 'da_login', 'da_pass_recuperar')
        ->orderby('id','ASC')
        ->get();

        $contador = 1;

        foreach ($listado_usuario as $lista){

          $usuario = tab_usuarios::find( $lista->id );
      		$usuario->da_password = bcrypt($lista->da_pass_recuperar);
      		$usuario->save();

          DB::commit();

          $this->info($contador.'- Usuario: '.$lista->da_login.' contraseña cambiada correctamente.');

          $contador = $contador+1;

        }
      } catch (\Illuminate\Database\QueryException $e) {
        DB::rollback();
        $this->info(utf8_encode( $e->getMessage()));
      }
    }
}
