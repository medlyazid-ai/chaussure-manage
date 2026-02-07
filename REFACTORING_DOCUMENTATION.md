# 🚚 Refactoring: Gestion Dynamique par Société de Livraison

## Vue d'ensemble du Changement

### Objectif
Transformer le système de gestion de stock d'un modèle basé sur les **destinations géographiques (pays)** vers un modèle basé sur les **sociétés de livraison**.

### Problème Résolu
**Avant**: Le stock était lié aux pays de destination
- ❌ Manque de flexibilité
- ❌ Ne reflète pas la réalité opérationnelle
- ❌ Difficile de suivre qui gère vraiment le stock

**Après**: Le stock est géré par société de livraison
- ✅ Plus dynamique et flexible
- ✅ Reflète la réalité: la société de livraison récupère et gère le stock
- ✅ Meilleure traçabilité de la chaîne logistique
- ✅ Simplifie la gestion pour plusieurs destinations via une même société

## 📊 Architecture Technique

### Modifications de la Base de Données

#### Nouvelles Colonnes
```sql
orders.transport_id           -- Société de livraison pour la commande
stock_adjustments.transport_id -- Ajustements par société
client_sales.transport_id      -- Ventes associées à une société
```

#### Nouvelle Table
```sql
transport_stocks -- Stock par société de livraison
  - transport_id
  - variant_id
  - quantity
```

#### Nouvelle Vue
```sql
transport_stock_view -- Calcul du stock en temps réel par société
  - transport_id
  - variant_id
  - product_name, size, color
  - total_received (reçu)
  - total_sold (vendu)
  - manual_adjustment (ajustements manuels)
  - current_stock (stock actuel calculé)
```

### Flux de Données

#### Ancien Flux (Pays)
```
Commande → Pays de destination → Stock par pays → Vente dans le pays
```

#### Nouveau Flux (Transport)
```
Commande → Société de livraison → Stock géré par la société → Vente via la société
```

### Compatibilité Descendante

Le système fonctionne en **mode hybride**:

```php
// Les modèles détectent automatiquement le mode
if (isset($data['transport_id'])) {
    // Mode transport (nouveau)
} else {
    // Mode pays (legacy)
}
```

## 🎯 Fonctionnalités Impactées

### 1. Gestion des Stocks

#### Vue Globale
- **Avant**: Stocks groupés par pays avec drapeaux
- **Après**: Stocks groupés par société de livraison avec types de transport

#### Vue Détaillée
- **Avant**: `/stocks/country/:id` - Stock d'un pays
- **Après**: `/stocks/transport/:id` - Stock d'une société

#### Ajustements
- **Avant**: Ajustement lié à un pays
- **Après**: Ajustement lié à une société de livraison

### 2. Commandes

#### Création
- **Avant**: Sélection obligatoire du pays de destination
- **Après**: Sélection de la société de livraison (pays optionnel)

#### Affichage
- **Avant**: "Destination: 🇬🇳 Guinée"
- **Après**: "Transport: 🚚 Cargo (Routier)"

### 3. Ventes Client

#### Sélection de Destination
- **Nouveau**: Choix entre société de livraison (recommandé) ou pays (legacy)
- Interface à deux options pour permettre la transition

#### Enregistrement
- **Avant**: Vente liée à un pays
- **Après**: Vente liée à une société de livraison (ou pays si legacy)

### 4. Envois (Shipments)

- **Impact minimal**: Les envois utilisaient déjà `transport_id`
- **Bonus**: Cohérence améliorée dans tout le système

## 💻 Changements de Code

### Modèles Mis à Jour

#### Order.php
```php
// Nouvelles méthodes
public static function allWithTransport()
public static function create($data) // Support transport_id et country_id

// Méthodes modifiées pour compatibilité
public static function allWithCountry() // COALESCE transport/country
public static function findWithSupplier($orderId) // LEFT JOIN transport
```

#### RealStock.php
```php
// Nouvelles méthodes
public static function getAvailableVariantsByTransport($transportId)
public static function getByTransport($transportId)
private static function checkViewExists($viewName) // Détection auto

// Méthodes legacy maintenues
public static function getAvailableVariantsByCountry($countryId)
public static function getByCountry($countryId)
```

#### StockAdjustment.php
```php
// Nouvelle méthode
public static function adjustByTransport($transportId, $variantId, $quantity, $reason)
public static function getByTransportAndVariant($transportId, $variantId)

// Méthode legacy maintenue
public static function adjust($countryId, $variantId, $quantity, $reason)
```

#### ClientSale.php
```php
// Nouvelle méthode
public static function createWithTransport($saleDate, $transportId, $customerName, $notes, $proofPath)

// Méthodes modifiées
public static function getAllWithCountry() // LEFT JOIN transport + country
public static function findWithCountry($id) // COALESCE transport/country
```

### Contrôleurs Mis à Jour

#### StockController.php
```php
// Détection automatique du mode
function listRealStocks() {
    if ($useTransport) {
        include 'views/stocks/overview_transport.php';
    } else {
        include 'views/stocks/overview.php';
    }
}

// Nouvelle fonction
function showTransportStock($transportId)

// Fonction modifiée
function adjustStock() // Support transport_id ou country_id
```

#### ClientSaleController.php
```php
// Fonction modifiée pour support dual
function createClientSale($countryId = null) {
    $transportId = $_GET['transport_id'] ?? null;
    // Logique adaptative...
}

function storeClientSale() {
    if ($transportId) {
        ClientSale::createWithTransport(...);
    } else {
        ClientSale::create(...);
    }
}
```

### Nouvelles Vues

1. **views/stocks/overview_transport.php**
   - Affichage des stocks par société de livraison
   - Colonnes: Société, Type, Reçu, Vendu, Ajustements, Stock

2. **views/stocks/transport.php**
   - Détail du stock d'une société
   - Formulaires d'ajustement par société
   - Historique des ajustements

3. **views/client_sales/select_destination.php**
   - Interface de sélection: société ou pays
   - Deux formulaires côte à côte
   - Message explicatif sur le nouveau système

### Routes Ajoutées

```php
case 'stocks':
    case 'transport': showTransportStock($id); // NOUVEAU
```

## 🔄 Migration des Données

### Stratégie de Migration

1. **Ajout de colonnes** (non-destructif)
2. **Migration des données existantes**
   - Mapper pays → transport via shipments existants
   - Utiliser transport par défaut si pas de mapping
3. **Création de la nouvelle vue**
4. **Vérification et tests**
5. **Nettoyage optionnel** (après validation)

### Correspondance Automatique

Le script de migration:
1. Trouve le premier transport utilisé pour chaque pays
2. Assigne ce transport aux commandes du pays
3. Migre les stocks country_stocks → transport_stocks
4. Met à jour les ajustements et ventes

### Rollback Possible

- Les anciennes colonnes sont conservées
- Possibilité de revenir à l'ancien système
- Aucune perte de données

## 📱 Interface Utilisateur

### Changements Visuels

#### Page Stocks
**Avant**:
```
🇬🇳 Guinée    | 500 reçus | 200 vendus | 300 stock
🇨🇮 Côte d'Ivoire | 400 reçus | 150 vendus | 250 stock
```

**Après**:
```
🚚 Cargo (Routier)     | 500 reçus | 200 vendus | 300 stock
✈️ Nahda Business (Aérien) | 400 reçus | 150 vendus | 250 stock
```

#### Page Ventes Client
**Nouvelle interface**:
```
┌─────────────────────────────────────┐
│   Enregistrer une Vente Client     │
├─────────────────────────────────────┤
│ 🚚 Par Société de Livraison        │
│ [Sélectionner: Cargo ▼]            │
│ [Continuer →]                       │
│                                     │
│ 🌍 Par Pays (Legacy)                │
│ [Sélectionner: Guinée ▼]           │
│ [Continuer →]                       │
└─────────────────────────────────────┘
```

## 🧪 Tests Recommandés

### Tests Fonctionnels

1. **Stock par Transport**
   ```
   ✓ Affichage de la liste des sociétés
   ✓ Détail du stock d'une société
   ✓ Ajustement de stock par société
   ✓ Calcul correct du stock actuel
   ```

2. **Création de Commande**
   ```
   ✓ Sélection de la société de livraison
   ✓ Sauvegarde avec transport_id
   ✓ Affichage correct dans la liste
   ```

3. **Vente Client**
   ```
   ✓ Sélection société ou pays
   ✓ Chargement des variantes correctes
   ✓ Enregistrement avec le bon ID
   ✓ Déduction du stock approprié
   ```

### Tests de Compatibilité

1. **Données Anciennes**
   ```
   ✓ Commandes avec country_id s'affichent
   ✓ Ventes avec country_id fonctionnent
   ✓ Stocks par pays encore accessibles
   ```

2. **Migration**
   ```
   ✓ Aucune perte de données
   ✓ Correspondances correctes
   ✓ Vue transport_stock_view calculée
   ```

## 🎓 Formation Utilisateurs

### Nouveaux Concepts

1. **Stock par Société**
   - Le stock n'est plus "dans un pays"
   - Le stock est "géré par une société de livraison"
   - Plus proche de la réalité logistique

2. **Workflow Mis à Jour**
   ```
   1. Commander auprès du fournisseur
   2. Spécifier la société de livraison
   3. Suivre l'envoi (shipment) par cette société
   4. Stock attribué à cette société
   5. Ventes enregistrées via cette société
   ```

### Guide Rapide

**Pour créer une vente**:
1. Aller sur "Ventes Client"
2. Cliquer "Nouvelle Vente"
3. Choisir "Par Société de Livraison" (recommandé)
4. Sélectionner la société (ex: Cargo)
5. Remplir la vente normalement

## 📊 Métriques et KPI

### Nouvelles Métriques Disponibles

```sql
-- Stock par société de livraison
SELECT t.name, SUM(ts.quantity) as stock_total
FROM transport_stocks ts
JOIN transports t ON ts.transport_id = t.id
GROUP BY t.id;

-- Performance par société
SELECT 
    t.name,
    COUNT(DISTINCT o.id) as nb_commandes,
    COUNT(DISTINCT cs.id) as nb_ventes,
    SUM(ts.quantity) as stock_actuel
FROM transports t
LEFT JOIN orders o ON o.transport_id = t.id
LEFT JOIN client_sales cs ON cs.transport_id = t.id
LEFT JOIN transport_stocks ts ON ts.transport_id = t.id
GROUP BY t.id;
```

## 🔐 Sécurité

### Considérations

- ✅ Validation des `transport_id` avant insertion
- ✅ Clés étrangères pour intégrité référentielle
- ✅ Vérification des permissions (futures)
- ✅ Audit trail maintenu via created_at

## 🚀 Prochaines Étapes

### Phase 1: Adoption (En cours)
- [x] Migration technique complète
- [x] Interface utilisateur mise à jour
- [ ] Formation des utilisateurs
- [ ] Tests en production

### Phase 2: Optimisation (Futur)
- [ ] Rapports spécifiques par société
- [ ] Dashboard dédié aux sociétés de livraison
- [ ] Notifications par société
- [ ] Historique détaillé

### Phase 3: Nettoyage (Optionnel)
- [ ] Supprimer les colonnes country_id si plus utilisées
- [ ] Archiver les anciennes données
- [ ] Finaliser la documentation

## 📞 Support

### Questions Fréquentes

**Q: Puis-je encore utiliser le système par pays?**
R: Oui, le mode legacy est maintenu pour la compatibilité.

**Q: Que se passe-t-il si je supprime une société de livraison?**
R: Les clés étrangères empêchent la suppression si des données sont liées.

**Q: Comment migrer mes anciennes commandes?**
R: Le script de migration le fait automatiquement. Voir `MIGRATION_GUIDE.md`.

**Q: Le calcul du stock change-t-il?**
R: Non, la formule reste la même: Reçu - Vendu + Ajustements.

## 📝 Changelog

### Version 2.0.0 - 2026-02-06

**Ajouté**:
- Support des sociétés de livraison pour le stock
- Vue `transport_stock_view`
- Table `transport_stocks`
- Colonnes `transport_id` sur orders, stock_adjustments, client_sales
- Interfaces pour sélection société/pays
- Mode hybride pour transition

**Modifié**:
- Tous les modèles pour support dual
- Contrôleurs pour détection automatique
- Vues pour affichage adaptatif

**Maintenu**:
- Compatibilité totale avec données existantes
- Toutes les fonctionnalités legacy
- Possibilité de rollback

---

**Note**: Ce refactoring est **non-destructif** et conçu pour une **transition en douceur**. Le système fonctionne en mode hybride jusqu'à migration complète des données et validation par les utilisateurs.
