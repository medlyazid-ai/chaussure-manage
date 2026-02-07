# 🔄 Migration: Pays → Sociétés de Livraison

## Vue d'ensemble

Cette migration transforme le système de gestion de stock basé sur les **destinations (pays)** vers un système basé sur les **sociétés de livraison (transports)**.

### Pourquoi cette migration ?

**Avant**: Le stock était suivi par destination géographique (pays)
- Problème: Pas assez dynamique
- Limitation: Les stocks étaient liés à des lieux fixes

**Après**: Le stock est suivi par société de livraison (transport)
- ✅ Plus dynamique et flexible
- ✅ Reflète mieux la réalité: la société de livraison gère et récupère le stock
- ✅ Permet une meilleure traçabilité

## 📋 Étapes de Migration

### 1. Sauvegarde (CRITIQUE)

```bash
# Sauvegarder la base de données AVANT toute migration
mysqldump -u username -p database_name > backup_before_transport_migration.sql
```

### 2. Exécuter la Migration

```bash
# Se connecter à MySQL
mysql -u username -p database_name

# Exécuter le script de migration
source sql/migration_country_to_transport.sql
```

Ou via phpMyAdmin:
1. Sélectionner votre base de données
2. Aller dans l'onglet "SQL"
3. Copier/coller le contenu de `sql/migration_country_to_transport.sql`
4. Cliquer sur "Exécuter"

### 3. Vérification Post-Migration

```sql
-- Vérifier que les colonnes ont été ajoutées
SHOW COLUMNS FROM orders LIKE 'transport_id';
SHOW COLUMNS FROM stock_adjustments LIKE 'transport_id';
SHOW COLUMNS FROM client_sales LIKE 'transport_id';

-- Vérifier que la nouvelle table existe
SHOW TABLES LIKE 'transport_stocks';

-- Vérifier que la vue existe
SHOW CREATE VIEW transport_stock_view;

-- Vérifier la migration des données
SELECT COUNT(*) as total_orders, 
       COUNT(transport_id) as orders_with_transport,
       COUNT(country_id) as orders_with_country
FROM orders;
```

### 4. Test du Système

1. **Test des stocks**:
   - Aller sur `?route=stocks`
   - Vérifier que l'affichage se fait par société de livraison
   - Cliquer sur "Voir détail" pour une société

2. **Test des commandes**:
   - Créer une nouvelle commande
   - Vérifier que la société de livraison peut être sélectionnée

3. **Test des ventes client**:
   - Aller sur `?route=client_sales/create`
   - Vérifier les deux options: par société ou par pays
   - Créer une vente avec une société de livraison

## 🔧 Personnalisation de la Migration

### Mapper les pays aux transports

Par défaut, la migration utilise le premier transport associé à chaque pays. Pour une migration personnalisée:

```sql
-- Option 1: Créer une table de correspondance temporaire
CREATE TEMPORARY TABLE country_transport_mapping (
    country_id INT,
    transport_id INT
);

-- Définir vos correspondances
INSERT INTO country_transport_mapping VALUES
(1, 1),  -- Guinée → Cargo
(2, 3),  -- Côte d'Ivoire → Nahda Business
(3, 4);  -- Mali → Mali Transport

-- Appliquer la correspondance aux commandes
UPDATE orders o
JOIN country_transport_mapping ctm ON o.country_id = ctm.country_id
SET o.transport_id = ctm.transport_id;
```

### Ajuster les données migrées

```sql
-- Vérifier les commandes sans transport assigné
SELECT * FROM orders WHERE transport_id IS NULL;

-- Assigner manuellement un transport
UPDATE orders SET transport_id = 1 WHERE id = 123;

-- Vérifier les ventes migrées
SELECT cs.*, t.name as transport_name 
FROM client_sales cs 
LEFT JOIN transports t ON cs.transport_id = t.id;
```

## 🔄 Compatibilité Descendante

Le système est conçu pour fonctionner en **mode hybride** pendant la transition:

### Mode Hybride Actif

- ✅ Les anciennes données (avec `country_id`) continuent de fonctionner
- ✅ Les nouvelles données peuvent utiliser `transport_id`
- ✅ Les vues affichent correctement les deux types de données
- ✅ Les modèles détectent automatiquement quel système utiliser

### Basculement Progressif

1. **Phase 1**: Migration exécutée, système en mode hybride
2. **Phase 2**: Nouvelles entrées utilisent `transport_id`
3. **Phase 3**: Migration complète des anciennes données
4. **Phase 4** (optionnel): Suppression des colonnes `country_id`

## 🧹 Nettoyage Post-Migration (Optionnel)

Une fois que vous avez vérifié que tout fonctionne:

```sql
-- ⚠️ ATTENTION: N'exécutez ceci qu'après vérification complète!

-- Supprimer les anciennes colonnes
ALTER TABLE orders DROP FOREIGN KEY fk_orders_country;
ALTER TABLE orders DROP COLUMN country_id;

ALTER TABLE stock_adjustments DROP COLUMN country_id;
ALTER TABLE client_sales DROP COLUMN country_id;

-- Supprimer l'ancienne table country_stocks
DROP TABLE IF EXISTS country_stocks;

-- Supprimer l'ancienne vue
DROP VIEW IF EXISTS real_stock_view;

-- Supprimer la table countries si plus utilisée (⚠️ prudence!)
-- DROP TABLE IF EXISTS countries;
```

## 🔙 Rollback (En cas de problème)

Si vous rencontrez des problèmes:

```bash
# Restaurer la sauvegarde
mysql -u username -p database_name < backup_before_transport_migration.sql
```

Ou pour un rollback partiel:

```sql
-- Supprimer les nouvelles colonnes
ALTER TABLE orders DROP COLUMN transport_id;
ALTER TABLE stock_adjustments DROP COLUMN transport_id;
ALTER TABLE client_sales DROP COLUMN transport_id;

-- Supprimer la nouvelle table
DROP TABLE IF EXISTS transport_stocks;

-- Supprimer la nouvelle vue
DROP VIEW IF EXISTS transport_stock_view;
```

## 📊 Vérification de l'Intégrité des Données

```sql
-- Vérifier que tous les stocks sont cohérents
SELECT 
    t.name as transport_name,
    COUNT(ts.id) as variants_count,
    SUM(ts.quantity) as total_quantity
FROM transport_stocks ts
JOIN transports t ON ts.transport_id = t.id
GROUP BY t.id;

-- Vérifier les ventes par transport
SELECT 
    t.name as transport_name,
    COUNT(cs.id) as sales_count
FROM client_sales cs
LEFT JOIN transports t ON cs.transport_id = t.id
GROUP BY cs.transport_id;

-- Vérifier les ajustements de stock
SELECT 
    t.name as transport_name,
    COUNT(sa.id) as adjustments_count,
    SUM(sa.adjusted_quantity) as total_adjustments
FROM stock_adjustments sa
LEFT JOIN transports t ON sa.transport_id = t.id
GROUP BY sa.transport_id;
```

## 💡 Bonnes Pratiques

1. **Toujours sauvegarder** avant la migration
2. **Tester sur un environnement de développement** d'abord
3. **Vérifier les données** après la migration
4. **Garder les colonnes legacy** pendant au moins 1 mois
5. **Former les utilisateurs** au nouveau système

## 🆘 Support

Si vous rencontrez des problèmes:

1. Vérifier les logs PHP: `/var/log/apache2/error.log` ou équivalent
2. Vérifier les logs MySQL
3. Consulter le fichier `TROUBLESHOOTING.md`
4. Restaurer la sauvegarde si nécessaire

## ✅ Checklist de Migration

- [ ] Sauvegarde de la base de données créée
- [ ] Script de migration exécuté sans erreur
- [ ] Colonnes `transport_id` ajoutées aux tables
- [ ] Table `transport_stocks` créée
- [ ] Vue `transport_stock_view` créée
- [ ] Données migrées (orders, sales, adjustments)
- [ ] Tests effectués:
  - [ ] Affichage des stocks par transport
  - [ ] Création d'une commande avec transport
  - [ ] Création d'une vente avec transport
  - [ ] Ajustement de stock par transport
- [ ] Vérification de l'intégrité des données
- [ ] Formation des utilisateurs
- [ ] Documentation mise à jour

## 📅 Historique

- **2026-02-06**: Création de la migration pays → transports
- **Version**: 1.0.0
- **Auteur**: Équipe de développement

---

**Note**: Cette migration est conçue pour être **non-destructive** et **réversible**. Les anciennes colonnes et tables sont conservées jusqu'à ce que vous soyez sûr que tout fonctionne correctement.
