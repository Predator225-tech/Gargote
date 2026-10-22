<?php

namespace App\Controllers\Api;
use App\Models\IngredientModel;use App\Services\StockService;
class Ingredients extends ApiController {
 public function index(){return $this->respond(model(IngredientModel::class)->lister());}
 public function mouvements($id){$m=model(IngredientModel::class);return !$m->find($id)?$this->erreur('Ingrédient introuvable.',404):$this->respond($m->mouvements((int)$id));}
 public function approvisionnement($id){$m=model(IngredientModel::class);if(!$m->find($id))return $this->erreur('Ingrédient introuvable.',404);$q=$this->donneesJson()['quantite']??null;if(!is_numeric($q)||$q<=0||$q>500||round((float)$q,3)<=0)return $this->erreurValidation(['quantite'=>'Quantité positive, de 0,001 à 500, avec un point décimal.']);(new StockService())->approvisionner((int)$id,round((float)$q,3));return $this->respond($m->find($id));}
}
