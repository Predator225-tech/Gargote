<?php

namespace App\Services;
use App\Exceptions\RegleMetierException;
use App\Models\{PlatModel,CommandeModel,CommandeLigneModel,ServeurModel};
class CommandeService {
 private const TRANSITIONS=['en_cours'=>['servie','annulee'],'servie'=>['payee'],'payee'=>[],'annulee'=>[]];
 public static function transitions(string $statut): array {return self::TRANSITIONS[$statut]??[];}
 private function entier($v): bool {return !is_bool($v) && (is_int($v)||(is_string($v)&&preg_match('/^[0-9]+$/D',$v)));}
 public function creer(array $d): int {
 $cfg=config('Gargote');$table=$d['numero_table']??null;$sid=$d['id_serveur']??null;
 if(!$this->entier($table)||$table<1||$table>$cfg->nombreTables) throw new RegleMetierException('Indiquez une table entre 1 et '.$cfg->nombreTables.'.',['numero_table'=>'Numéro de table invalide.']);
 if(!$this->entier($sid)||!($s=model(ServeurModel::class)->find($sid))||!$s['actif']) throw new RegleMetierException('Ce serveur n’existe pas ou ne travaille plus ici.',['id_serveur'=>'Choisissez un serveur actif.']);
 if(!is_array($d['lignes']??null)||!array_is_list($d['lignes'])||!$d['lignes']) throw new RegleMetierException('La commande est vide.',['lignes'=>'Ajoutez au moins un plat.']);
 $lignes=[];foreach($d['lignes'] as $i=>$l) { $q=is_array($l)?($l['quantite']??null):null;$pid=is_array($l)?($l['id_plat']??null):null;
 if(!$this->entier($q)||$q<1||$q>$cfg->quantiteMax) throw new RegleMetierException('Quantité invalide.',["lignes.$i.quantite"=>'Choisissez entre 1 et '.$cfg->quantiteMax.'.']);
 if(!$this->entier($pid)||!($p=model(PlatModel::class)->find($pid))) throw new RegleMetierException('Plat inconnu.',["lignes.$i.id_plat"=>'Ce plat n’existe pas.']);
 if(!$p['disponible']) throw new RegleMetierException('Le '.$p['nom'].' est épuisé.',[],409);
 $total=($lignes[$pid]['quantite']??0)+(int)$q;if($total>$cfg->quantiteMax) throw new RegleMetierException('Quantité cumulée excessive.',["lignes.$i.quantite"=>'Maximum '.$cfg->quantiteMax.' par plat.']);
 $lignes[$pid]=['id_plat'=>(int)$pid,'quantite'=>$total,'prix_unitaire'=>$p['prix']]; }
 $db=db_connect();$ouverte=$db->table('commande')->where('numero_table',$table)->whereIn('statut',['en_cours','servie'])->get()->getRowArray();if($ouverte) throw new RegleMetierException('La table '.$table.' a déjà une commande en cours (n° '.$ouverte['id'].').',[],409);
 $stock=new StockService();$besoins=$stock->besoins(array_values($lignes));$stock->verifier($besoins);
 $db->transException(true)->transBegin();try { $stock->verrouiller($besoins);$stock->verifier($besoins);$id=model(CommandeModel::class)->insert(['numero_table'=>(int)$table,'id_serveur'=>(int)$sid],true);foreach($lignes as $l) model(CommandeLigneModel::class)->insert($l+['id_commande'=>$id]);$stock->sortir($besoins,(int)$id);$db->transCommit();return (int)$id;
 } catch(\Throwable $e) {$db->transRollback();if($e instanceof RegleMetierException)throw $e;if(str_contains($e->getMessage(),'ux_commande_table_ouverte'))throw new RegleMetierException('Cette table a déjà une commande ouverte.',[],409);throw $e;}
 }
 public function changerStatut(int $id,string $statut,?string $modePaiement): void { $db=db_connect();$db->transException(true)->transBegin();try { $c=$db->query('SELECT * FROM commande WHERE id=? FOR UPDATE',[$id])->getRowArray();if(!$c)throw new RegleMetierException('Commande introuvable.',[],404);
 if(!in_array($statut,self::transitions($c['statut']),true))throw new RegleMetierException('Cette transition est impossible.',[],409);
 $d=['statut'=>$statut];if($statut==='payee') {if(!array_key_exists($modePaiement??'',config('Gargote')->modesPaiement))throw new RegleMetierException('Choisissez un mode de paiement.',['mode_paiement'=>'Mode de paiement invalide.']);$d+=['mode_paiement'=>$modePaiement,'date_paiement'=>date('Y-m-d H:i:s')];}
 if($statut==='annulee')(new StockService())->reintegrer($id);model(CommandeModel::class)->update($id,$d);$db->transCommit();}catch(\Throwable $e){$db->transRollback();throw $e;}}
}
