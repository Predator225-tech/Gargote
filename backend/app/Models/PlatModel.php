<?php

namespace App\Models;

use CodeIgniter\Model;

class PlatModel extends Model
{
    protected $table      = 'plat';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // PostgreSQL renvoie les nombres sous forme de texte : on les convertit.
    protected array $casts = [
        'id'           => 'int',
        'disponible' => 'boolean',
        'portions' => '?int',
        'prix'         => 'int',
        'id_categorie' => 'int',
    ];

    // Seuls ces champs peuvent être écrits par insert() / update().
    // Un champ ajouté dans la table doit AUSSI être ajouté ici.
    protected $allowedFields = ['nom', 'prix', 'id_categorie', 'description', 'disponible'];

    // Règles de validation : vérifiées automatiquement avant insert() / update().
    // Liste des règles disponibles :
    // https://codeigniter.com/user_guide/libraries/validation.html#available-rules
    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'description' => 'permit_empty|max_length[255]',
        'disponible' => 'if_exist|est_booleen',
        'nom'          => 'required|min_length[2]|max_length[100]|is_unique[plat.nom,id,{id}]',
        'prix'         => 'required|is_natural_no_zero|less_than_equal_to[100000]',
        'id_categorie' => 'required|is_natural_no_zero|is_not_unique[categorie.id]',
    ];

    protected $validationMessages = [
        'nom' => [
            'required'   => 'Le nom du plat est obligatoire.',
            'is_unique' => 'Ce nom de plat existe déjà.',
            'max_length' => 'Le nom ne doit pas dépasser 100 caractères.',
        ],
        'prix' => [
            'required' => 'Le prix est obligatoire.',
            'is_natural_no_zero' => 'Le prix doit être un entier strictement positif.',
            'less_than_equal_to' => 'Le prix ne peut dépasser 100 000 Ar.',
            'integer'  => 'Le prix doit être un nombre entier (en Ariary).',
        ],
        'id_categorie' => [
            'required' => 'Choisissez une catégorie.',
            'is_not_unique' => 'Cette catégorie n’existe pas.',
        ],
    ];

    /**
     * Liste des plats avec le libellé de leur catégorie.
     * Si $idCategorie est fourni, ne garde que cette catégorie.
     */
    public function listerAvecCategorie(?int $idCategorie = null, ?bool $disponible = null): array
    {
        $this->select('plat.*, categorie.libelle AS categorie, vp.portions')
            ->join('v_portions_possibles vp', 'vp.id_plat = plat.id', 'left')
            ->join('categorie', 'categorie.id = plat.id_categorie')
            ->orderBy('categorie.libelle')
            ->orderBy('plat.nom');

        if ($idCategorie !== null) {
            $this->where('plat.id_categorie', $idCategorie);
        }

        if ($disponible !== null) $this->where('plat.disponible', $disponible);
        return $this->findAll();
    }
}
