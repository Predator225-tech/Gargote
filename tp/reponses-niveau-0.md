# Niveau 0 — Prise en main : Réponses

## 0.1 Lancer l’application

La base PostgreSQL de démonstration a été démarrée avec Docker et les scripts des niveaux 0 à 3 se sont tous exécutés sans erreur. L’API renvoie `200` sur `/api/plats` (15 plats), Vite renvoie `200` sur `/` et son proxy `/api/plats` renvoie aussi `200`. Le serveur de commande et le formulaire sont présents ; je n’ai pas créé de commande de test. Pour repartir de zéro plus tard, `database/reset.sh 3` recrée `public` et efface les données de cette base.

## 0.2 Lire le schéma relationnel

1. Schéma des quatre tables (`id` est la clé primaire et `#` une clé étrangère) :

   - `categorie` (**id**, `libelle`)
   - `plat` (**id**, `nom`, `prix`, `#id_categorie`)
   - `commande` (**id**, `date_commande`, `numero_table`)
   - `commande_ligne` (**id**, `#id_commande`, `#id_plat`, `quantite`, `prix_unitaire`)

2. `prix_unitaire` conserve le prix historique à la date de la commande. Si le prix d’un café passe de 1 000 Ar à 1 500 Ar, les anciennes lignes de commande restent à 1 000 Ar.

3. Supprimer une commande supprime aussi ses lignes : la clé étrangère `commande_ligne.id_commande` porte `ON DELETE CASCADE` ([`database/01_schema.sql`](../database/01_schema.sql), ligne 38).

4. Le schéma de départ accepte notamment : un prix négatif, une quantité nulle ou négative, et un nom vide. Il ne définit pas encore de contraintes `CHECK` pour ces cas (un `NOT NULL` seul n’interdit pas une chaîne vide).

## 0.3 Interroger l’API à la main

Les requêtes ont été exécutées contre la base de démonstration, chargée jusqu’au niveau 3. *Mofo gasy* existe déjà dans ces données, donc la requête b, censée être une création valide au niveau 0, est rejetée comme doublon ici.

| Requête | Résultat attendu au niveau 0 |
|---|---|
| a) `GET /api/plats?categorie=2` | `200 OK`, JSON contenant *Mi sao* et *Soupe chinoise* |
| b) création de *Mofo gasy* | `422`, `{"message":"Données invalides.","errors":{"nom":"Ce nom de plat existe déjà."}}` |
| c) nom vide et prix texte | `422`, validation sur `nom`, `prix` et `id_categorie` |
| d) suppression de `/api/plats/2` | `409 Conflict`, `Ce plat figure déjà dans des commandes, il ne peut pas être supprimé.` |
| e) `GET /api/commandes/999` | `404 Not Found`, `{"message":"Commande 999 introuvable.","errors":{}}` |

5. Une création réussie renvoie `201 Created` car elle crée une nouvelle ressource. Dans la base actuelle, b renvoie toutefois `422` : le nom *Mofo gasy* existe déjà, ajouté par les données du niveau 1. Sur une base limitée au niveau 0, cette requête est nouvelle et doit renvoyer `201`.

6. C’est PostgreSQL qui refuse la suppression à cause de la clé étrangère depuis `commande_ligne.id_plat`. Dans [`backend/app/Controllers/Api/Plats.php`](../backend/app/Controllers/Api/Plats.php), méthode `delete()`, le `catch (\\Throwable $e)` transforme ce refus en `409` avec le message « Ce plat figure déjà dans des commandes, il ne peut pas être supprimé. »

7. Exemple de création avec deux *Romazava* et un *Café* (IDs de démonstration 4 et 10, serveur actif Tiana ID 2 après les migrations des niveaux 1–2) :

   ```bash
   curl -i -X POST http://localhost:8080/api/commandes \
     -H "Content-Type: application/json" \
     -d '{"numero_table":6,"id_serveur":2,"lignes":[{"id_plat":4,"quantite":2},{"id_plat":10,"quantite":1}]}'
   ```

   Le code actuel exige `id_serveur` ([`backend/app/Services/CommandeService.php`](../backend/app/Services/CommandeService.php), lignes 10–14). Choisir une table libre ; le numéro 6 peut déjà être occupé selon les commandes présentes.

## 0.4 Suivre une requête de bout en bout

| # | Étape | Fichier | Fonction / ligne |
|---|---|---|---|
| 1 | Le formulaire est soumis | `frontend/src/components/PlatForm.vue` | `enregistrer()`, appelée par `@submit.prevent` |
| 2 | La requête HTTP est construite | `frontend/src/api.js` | `requete()` |
| 3 | Vite transmet `/api/...` au port 8080 | `frontend/vite.config.js` | configuration `server.proxy['/api']` |
| 4 | CodeIgniter choisit le contrôleur | `backend/app/Config/Routes.php` | route `POST api/plats` vers `Plats::create` |
| 5 | Le contrôleur lit le JSON | `backend/app/Controllers/Api/ApiController.php` | `donneesJson()` |
| 6 | Les règles de validation sont vérifiées | `backend/app/Controllers/Api/Plats.php` puis `backend/app/Models/PlatModel.php` | `create()` appelle `insert()` ; le modèle valide |
| 7 | La ligne est insérée | `backend/app/Controllers/Api/Plats.php` | `create()`, appel à `$this->plats->insert($donnees)` |
| 8 | Le formulaire prévient l’écran parent | `frontend/src/components/PlatForm.vue` | `emit('enregistre', resultat)` |
| 9 | L’écran recharge la liste | `frontend/src/views/CarteView.vue` | `surEnregistrement()` appelle `charger()` |

8. La création d’une commande est orchestrée par `CommandeService::creer()` dans `backend/app/Services/CommandeService.php`. Le contrôleur `Commandes::create()` délègue au service pour garder les règles métier et les écritures coordonnées hors du contrôleur HTTP.

## 0.5 Repérer les notions de Vue

| Notion | Exemple | Rôle |
|---|---|---|
| `ref()` | `frontend/src/App.vue`, `ongletActif` | Valeur réactive ; sa modification met à jour l’onglet actif affiché. |
| `computed()` | `frontend/src/App.vue`, `vueCourante` | Trouve le composant correspondant à l’onglet actif. |
| `v-for` | `frontend/src/App.vue`, boucle sur `onglets` | Affiche un bouton par onglet. |
| `v-if` / `v-else` | `frontend/src/components/Panier.vue`, autour de la liste | Affiche le message de panier vide ou la liste des lignes. |
| `v-model` | `frontend/src/components/PlatForm.vue`, champ `form.nom` | Synchronise la saisie du champ et la donnée réactive. |
| `defineProps` | `frontend/src/components/PlatForm.vue` | Reçoit le plat en édition et les catégories du parent. |
| `defineEmits` / `emit` | `frontend/src/components/PlatForm.vue` | Signale au parent l’enregistrement ou l’annulation. |
| `onMounted` | `frontend/src/views/CarteView.vue` | Charge les plats et catégories après l’affichage de l’écran. |

9. `Panier.vue` reste un composant d’affichage : il émet `plus` et `moins` afin que le parent décide de la modification. C’est `CommandeView.vue`, dans `ajouter()` et `retirer()`, qui modifie `panier` et donc les quantités.

10. Le `watch` dans `PlatForm.vue` observe `props.plat` et recopie les champs du plat choisi dans le formulaire. Sans lui, le formulaire ne suivrait pas le choix d’un autre plat après son affichage.

## 0.6 Premières modifications

La mention de l’enseigne a été remplacée par **« Chez Mandresy, Analakely »** dans `frontend/src/App.vue`.

*Goûter* était déjà présent dans les données du niveau 1. L’instruction SQL idempotente a confirmé sa présence sans créer de doublon ; `GET /api/categories` le retourne pour la liste déroulante :

```sql
INSERT INTO categorie (libelle) VALUES ('Goûter') ON CONFLICT (libelle) DO NOTHING;
```

11. Le menu est dynamique : `CarteView.vue` appelle `api.categories.lister()` au chargement ; cette fonction envoie `GET /api/categories` via `frontend/src/api.js`. La route est reliée à `Categories::index()` dans `backend/app/Config/Routes.php`. Le composant `PlatForm.vue` reçoit ensuite la liste par `:categories="categories"` et affiche une option par catégorie avec `v-for`. Aucune modification du code n’est nécessaire pour faire apparaître une nouvelle catégorie retournée par l’API.

## Vérifications restant à faire en local

- Passer une commande depuis l’interface et vérifier son apparition dans l’onglet **Commandes**.
