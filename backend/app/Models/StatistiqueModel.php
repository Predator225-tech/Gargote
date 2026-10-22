<?php

namespace App\Models;
use CodeIgniter\Model;
class StatistiqueModel extends Model {
 public function periode(string $du,string $au):array {
 $db=$this->db;$args=[$du,$au];$base=" FROM commande c JOIN commande_ligne l ON l.id_commande=c.id JOIN serveur s ON s.id=c.id_serveur JOIN plat p ON p.id=l.id_plat WHERE c.statut='payee' AND c.date_paiement::date BETWEEN ? AND ? ";
 $r=$db->query('SELECT COALESCE(SUM(l.quantite*l.prix_unitaire),0)::int chiffre_affaires,COUNT(DISTINCT c.id)::int nb_commandes'.$base,$args)->getRowArray();$r['chiffre_affaires']=(int)$r['chiffre_affaires'];$r['nb_commandes']=(int)$r['nb_commandes'];$r['panier_moyen']=$r['nb_commandes']?round($r['chiffre_affaires']/$r['nb_commandes']):0;
 $r['par_jour']=$db->query("SELECT d::date AS jour,COALESCE(v.chiffre_affaires,0)::int chiffre_affaires FROM generate_series(?::date,?::date,interval '1 day') d LEFT JOIN v_ventes_jour v ON v.jour=d::date ORDER BY d",$args)->getResultArray();
 $r['top_plats']=$db->query('SELECT p.nom plat,SUM(l.quantite)::int quantite,SUM(l.quantite*l.prix_unitaire)::int chiffre_affaires'.$base.'GROUP BY p.id ORDER BY chiffre_affaires DESC LIMIT 5',$args)->getResultArray();
 $r['par_serveur']=$db->query('SELECT s.nom serveur,COUNT(DISTINCT c.id)::int nb_commandes,SUM(l.quantite*l.prix_unitaire)::int chiffre_affaires'.$base.'GROUP BY s.id ORDER BY chiffre_affaires DESC',$args)->getResultArray();
 $r['par_mode']=$db->query('SELECT c.mode_paiement mode,COUNT(DISTINCT c.id)::int nb_commandes,SUM(l.quantite*l.prix_unitaire)::int chiffre_affaires'.$base.'GROUP BY c.mode_paiement ORDER BY chiffre_affaires DESC',$args)->getResultArray();return $r;
 }
}
