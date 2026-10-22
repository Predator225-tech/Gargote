<?php

namespace App\Models;
use CodeIgniter\Model;
class PlatDuJourModel extends Model {
 protected $table='plat_du_jour'; protected $primaryKey='date_jour';
 public function lire(bool $historique=false) { $q=$this->db->table('plat_du_jour j')->select('j.date_jour,p.*,v.portions')->join('plat p','p.id=j.id_plat')->join('v_portions_possibles v','v.id_plat=p.id','left');
 if($historique) $q->where('j.date_jour >=',date('Y-m-d',strtotime('-6 days')))->where('j.date_jour <=',date('Y-m-d')); else $q->where('j.date_jour',date('Y-m-d'));
 $rows=$q->orderBy('j.date_jour','DESC')->get()->getResultArray();
 foreach($rows as &$r) {foreach(['id','prix','id_categorie'] as $k) $r[$k]=(int)$r[$k]; $r['disponible']=in_array($r['disponible'],[true,'t','1',1],true);$r['portions']=$r['portions']===null?null:(int)$r['portions'];} return $historique?$rows:($rows[0]??null); }
 public function definir(int $id): void { $this->db->query('INSERT INTO plat_du_jour(date_jour,id_plat) VALUES(CURRENT_DATE,?) ON CONFLICT(date_jour) DO UPDATE SET id_plat=EXCLUDED.id_plat',[$id]); }
}
