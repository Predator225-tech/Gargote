<?php

namespace App\Controllers\Api;
use App\Models\StatistiqueModel;
class Statistiques extends ApiController {
 public function index(){$du=$this->request->getGet('du')??date('Y-m-d',strtotime('-29 days'));$au=$this->request->getGet('au')??date('Y-m-d');foreach([$du,$au] as $d){if(!is_string($d)||!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$d)||!($date=\DateTimeImmutable::createFromFormat('!Y-m-d',$d))||$date->format('Y-m-d')!==$d)return $this->erreurValidation(['periode'=>'Utilisez des dates valides au format AAAA-MM-JJ.']);}if($du>$au||(strtotime($au)-strtotime($du))/86400>365)return $this->erreurValidation(['periode'=>'La période doit être ordonnée et limitée à 366 jours.']);return $this->respond(model(StatistiqueModel::class)->periode($du,$au));}
}
