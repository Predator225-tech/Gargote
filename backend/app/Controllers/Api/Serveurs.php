<?php

namespace App\Controllers\Api;
use App\Models\ServeurModel;
class Serveurs extends ApiController {
 public function index(){$m=model(ServeurModel::class);if($this->request->getGet('tous')!=='1')$m->where('actif',true);return $this->respond($m->findAll());}
 public function create(){$m=model(ServeurModel::class);$id=$m->insert($this->donneesJson());return $id===false?$this->erreurValidation($m->errors()):$this->respondCreated($m->find($id));}
 public function update($id){$m=model(ServeurModel::class);if(!$m->find($id))return $this->erreur('Serveur introuvable.',404);$d=$this->donneesJson();$d['id']=(int)$id;return $m->update($id,$d)?$this->respond($m->find($id)):$this->erreurValidation($m->errors());}
}
