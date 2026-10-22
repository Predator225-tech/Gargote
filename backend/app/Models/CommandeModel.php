<?php

namespace App\Models;

use CodeIgniter\Model;

class CommandeModel extends Model
{
    protected $table         = 'commande';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['numero_table', 'date_commande', 'id_serveur', 'statut', 'mode_paiement', 'date_paiement'];

    // PostgreSQL renvoie les nombres sous forme de texte : on les convertit.
    protected array $casts = [
        'id'           => 'int',
        'numero_table' => 'int',
        'nb_lignes'    => 'int',
        'total'        => 'int',
    ];

    /**
     * Toutes les commandes, de la plus récente à la plus ancienne,
     * avec leur montant total calculé en SQL.
     */
    public function listerAvecTotal(array $filtres = []): array
    {
        foreach (['statut','date'] as $k) { if (!empty($filtres[$k])) $this->where($k === 'date' ? 'commande.date_commande::date' : 'commande.statut', $filtres[$k]); }
        return $this->select('commande.*, serveur.nom AS serveur')
            ->join('serveur','serveur.id=commande.id_serveur')
            ->select('COUNT(commande_ligne.id) AS nb_lignes')
            ->select('COALESCE(SUM(commande_ligne.quantite * commande_ligne.prix_unitaire), 0) AS total')
            ->join('commande_ligne', 'commande_ligne.id_commande = commande.id', 'left')
            ->groupBy('commande.id, serveur.nom')
            ->orderBy('commande.date_commande', 'DESC')
            ->findAll();
    }

    /** Une commande avec ses lignes (et le nom des plats). */
    public function detail(int $id): ?array
    {
        $commande = $this->find($id);
        if ($commande === null) {
            return null;
        }

        $commande['serveur'] = $this->db->table('serveur')->where('id',$commande['id_serveur'])->get()->getRowArray()['nom'];
        $commande['transitions'] = \App\Services\CommandeService::transitions($commande['statut']);
        $commande['lignes'] = $this->db->table('commande_ligne l')
            ->select('l.id, l.id_plat, p.nom AS plat, l.quantite, l.prix_unitaire')
            ->select('l.quantite * l.prix_unitaire AS sous_total', false)
            ->join('plat p', 'p.id = l.id_plat')
            ->where('l.id_commande', $id)
            ->orderBy('l.id')
            ->get()->getResultArray();

        // Conversion texte -> entier des colonnes numériques des lignes
        foreach ($commande['lignes'] as &$ligne) {
            foreach (['id', 'id_plat', 'quantite', 'prix_unitaire', 'sous_total'] as $col) {
                $ligne[$col] = (int) $ligne[$col];
            }
        }
        unset($ligne);

        $commande['total'] = array_sum(array_column($commande['lignes'], 'sous_total'));

        return $commande;
    }
}
