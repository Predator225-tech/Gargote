<?php

namespace App\Controllers\Api;
use App\Models\PlatDuJourModel;use App\Services\PlatDuJourService;use App\Exceptions\RegleMetierException;
class PlatDuJour extends ApiController {
 public function index(){return $this->response->setJSON(model(PlatDuJourModel::class)->lire());}
 public function historique(){return $this->respond(model(PlatDuJourModel::class)->lire(true));}
 public function update(){try{return $this->respond((new PlatDuJourService())->definir($this->donneesJson()['id_plat']??null));}catch(RegleMetierException $e){return $this->erreurMetier($e);}}
}
