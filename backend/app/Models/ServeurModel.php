<?php

namespace App\Models;
use CodeIgniter\Model;
class ServeurModel extends Model {
 protected $table='serveur'; protected $allowedFields=['nom','telephone','actif'];
 protected array $casts=['id'=>'int','actif'=>'boolean'];
 protected $validationRules=['id'=>'permit_empty|is_natural_no_zero','nom'=>'required|min_length[2]|max_length[50]','telephone'=>'required|telephone_mg|is_unique[serveur.telephone,id,{id}]','actif'=>'if_exist|est_booleen'];
}
