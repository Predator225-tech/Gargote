<?php

namespace App\Models;
use CodeIgniter\Model;
class IngredientModel extends Model {
 protected $table='ingredient';protected array $casts=['id'=>'int','stock'=>'float','seuil_alerte'=>'float','en_alerte'=>'boolean'];
 public function lister():array{return $this->select('*,stock<=seuil_alerte AS en_alerte',false)->orderBy('en_alerte','DESC')->orderBy('nom')->findAll();}
 public function mouvements(int $id):array{return $this->db->table('mouvement_stock')->where('id_ingredient',$id)->orderBy('date_mouvement','DESC')->orderBy('id','DESC')->get()->getResultArray();}
}
