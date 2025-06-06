# Changelog - Blog Medianimal

## [2024-12-03] - Refactoring du système d'images

### ✅ Ajouté
- **Nouveau système d'images** :
  - `imageMainPath` : Image principale obligatoire
  - `imageAltPath` : Image alternative optionnelle
  - Contraintes de validation strictes (JPEG/PNG/WebP, 2Mo max, 50k pixels min)
  
- **Système de publication robuste** :
  - `isPublished` : Contrôle de visibilité boolean
  - Méthode `isVisible()` : Logique de visibilité intelligente
  - Workflow éditorial : brouillon → préparation → planification → publication

- **Interface d'administration améliorée** :
  - Sections organisées (Publication, Images, Métadonnées)
  - Upload VichUploader intégré
  - Messages d'aide contextuels

### 🔧 Modifié
- **Contrôleur public** : Utilise la nouvelle logique `isVisible()`
- **Requêtes BDD** : Filtrage par `isPublished` + `publishedAt`
- **Validation du titre** : Avertissement à 110 caractères dans l'admin

### ❌ Supprimé
- **Ancien champ `image`** : Remplacé par `imageMainPath` et `imageAltPath`
- **Méthodes obsolètes** : `getImage()`, `setImage()`, `getImageFile()`, `setImageFile()`

### 🛠️ Technique
- **Extension PHP** : `fileinfo` activée pour VichUploader
- **Migrations** : 3 migrations appliquées
  - Ajout `imageMainPath` et `imageAltPath`
  - Ajout `isPublished`
  - Suppression `image`
- **Cache** : Vidé et validé
- **Schéma BDD** : Synchronisé et validé

### 📁 Structure finale
```
Article {
  // Base
  id, title, content
  
  // Publication
  createdAt, publishedAt, isPublished
  
  // Images
  imageMainPath, imageMainFile (obligatoire)
  imageAltPath, imageAltFile (optionnel)
}
```

### 🎯 Avantages
- **SEO optimisé** : Images publiques + contrôle indexation
- **Flexibilité éditoriale** : Brouillons, planification, publication
- **Sécurité** : Validation stricte des uploads
- **Maintenabilité** : Code propre, structure claire 