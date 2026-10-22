<?php

namespace App\Services;
use App\Exceptions\RegleMetierException;
use App\Models\{PlatModel,PlatDuJourModel};
class PlatDuJourService {
 public function definir($id) { if(filter_var($id,FILTER_VALIDATE_INT)===false || !($p=model(PlatModel::class)->find($id))) throw new RegleMetierException('Choisissez un plat existant.',['id_plat'=>'Plat inconnu.']);
 if(!$p['disponible']) throw new RegleMetierException('Le '.$p['nom'].' est épuisé : il ne peut pas être plat du jour.',[],409);
 model(PlatDuJourModel::class)->definir((int)$id); return model(PlatDuJourModel::class)->lire(); }
}
