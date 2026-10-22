<?php

namespace App\Services;
use App\Exceptions\RegleMetierException;
class StockService {
 public function besoins(array $lignes): array { $result=[];foreach($lignes as $l) { $rows=db_connect()->table('recette')->where('id_plat',$l['id_plat'])->get()->getResultArray(); foreach($rows as $r) $result[$r['id_ingredient']]=round(($result[$r['id_ingredient']]??0)+(float)$r['quantite']*$l['quantite'],3); } ksort($result);return $result; }
 public function verifier(array $besoins): void { $errors=[];foreach($besoins as $id=>$q) { $i=db_connect()->table('ingredient')->where('id',$id)->get()->getRowArray(); if((float)$i['stock']<$q) $errors[$i['nom']]='Il faut '.number_format($q,3,',','').' '.$i['unite'].', il en reste '.number_format((float)$i['stock'],3,',','').' '.$i['unite'].'.'; }if($errors) throw new RegleMetierException('Stock insuffisant pour cette commande.',$errors,409); }
 public function verrouiller(array $besoins): void { foreach(array_keys($besoins) as $id) db_connect()->query('SELECT id FROM ingredient WHERE id=? FOR UPDATE',[$id]); }
 private function mouvement(int $id,float $q,string $motif,?int $commande=null): void { $db=db_connect();$db->query('UPDATE ingredient SET stock=stock+? WHERE id=?',[$q,$id]);$db->table('mouvement_stock')->insert(['id_ingredient'=>$id,'quantite'=>$q,'motif'=>$motif,'id_commande'=>$commande]); }
 public function sortir(array $besoins,int $idCommande): void {foreach($besoins as $id=>$q) $this->mouvement($id,-$q,'commande',$idCommande);}
 public function reintegrer(int $idCommande): void { $rows=db_connect()->table('mouvement_stock')->where('id_commande',$idCommande)->where('motif','commande')->orderBy('id_ingredient')->get()->getResultArray();foreach($rows as $r) {$this->verrouiller([$r['id_ingredient']=>0]);$this->mouvement((int)$r['id_ingredient'],-(float)$r['quantite'],'annulation',$idCommande);} }
 public function approvisionner(int $idIngredient,float $quantite): void { $db=db_connect();$db->transException(true)->transStart();$this->verrouiller([$idIngredient=>0]);$this->mouvement($idIngredient,$quantite,'approvisionnement');$db->transComplete(); }
}
