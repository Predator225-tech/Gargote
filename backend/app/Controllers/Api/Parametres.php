<?php

namespace App\Controllers\Api;
class Parametres extends ApiController {public function index(){$c=config('Gargote');return $this->respond(['nombre_tables'=>$c->nombreTables,'quantite_max'=>$c->quantiteMax,'modes_paiement'=>$c->modesPaiement]);}}

