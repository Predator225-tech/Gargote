<?php

namespace App\Controllers\Api;

use App\Exceptions\RegleMetierException;
use App\Models\CommandeModel;
use App\Services\CommandeService;

class Commandes extends ApiController
{
    /** GET /api/commandes */
    public function index()
    {
        return $this->respond(model(CommandeModel::class)->listerAvecTotal($this->request->getGet()));
    }

    /** GET /api/commandes/{id} — la commande et ses lignes */
    public function show($id = null)
    {
        $commande = model(CommandeModel::class)->detail((int) $id);

        return $commande ? $this->respond($commande) : $this->erreur("Commande $id introuvable.", 404);
    }

    /**
     * POST /api/commandes
     * Corps attendu :
     * {
     *   "numero_table": 4,
     *   "lignes": [ { "id_plat": 2, "quantite": 1 }, ... ]
     * }
     */
    public function statut($id) {
        $d=$this->donneesJson();
        if(!is_string($d['statut']??null) || (isset($d['mode_paiement']) && !is_string($d['mode_paiement']))) return $this->erreurValidation(['statut'=>'Statut et paiement doivent être du texte.']);
        try {(new CommandeService())->changerStatut((int)$id,$d['statut'],$d['mode_paiement']??null);}catch(RegleMetierException $e){return $this->erreurMetier($e);}
        return $this->respond(model(CommandeModel::class)->detail((int)$id));
    }
    public function create()
    {
        try {
            $id = (new CommandeService())->creer($this->donneesJson());
        } catch (RegleMetierException $e) {
            return $this->erreurMetier($e);
        }

        return $this->respondCreated(model(CommandeModel::class)->detail($id));
    }
}
