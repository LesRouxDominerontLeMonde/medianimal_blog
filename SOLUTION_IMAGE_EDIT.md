# Solution : Problème d'édition des articles avec images

## 🚨 Problème initial
Lors de l'édition d'un article existant, même si l'article avait déjà une image principale en base de données, le formulaire bloquait la soumission avec l'erreur "Une image principale est obligatoire".

## 🔍 Cause du problème
La contrainte `@Assert\NotNull` sur le champ `imageMainFile` était trop stricte :
- Elle exigeait TOUJOURS un fichier uploadé
- Même lors de l'édition d'un article qui avait déjà une image
- Elle ne prenait pas en compte le fait qu'on peut vouloir garder l'image existante

## ✅ Solution implémentée

### 1. **Suppression de la contrainte NotNull**
```php
// AVANT (problématique)
#[Assert\NotNull(message: 'Une image principale est obligatoire')]
private ?File $imageMainFile = null;

// APRÈS (corrigé)
// Pas de NotNull sur imageMainFile
private ?File $imageMainFile = null;
```

### 2. **Validateur personnalisé intelligent**
Création de `RequiredMainImageValidator` qui vérifie :

#### **Pour un nouvel article** (ID = null) :
- ✅ Doit avoir soit `imageMainFile` (nouveau fichier) soit `imageMainPath` (existant)

#### **Pour un article existant** (ID ≠ null) :
- ✅ Peut garder l'image existante (`imageMainPath`)
- ✅ Peut uploader une nouvelle image (`imageMainFile`)
- ❌ Ne peut pas supprimer l'image sans la remplacer

### 3. **Logique de validation**
```php
public function validate($value, Constraint $constraint): void
{
    // Nouvel article : doit avoir une image
    if ($value->getId() === null && !$value->hasMainImage()) {
        // Erreur : image obligatoire
    }

    // Article existant : doit garder ou remplacer l'image
    if ($value->getId() !== null && 
        $value->getImageMainFile() === null && 
        empty($value->getImageMainPath())) {
        // Erreur : pas d'image du tout
    }
}
```

### 4. **Interface admin améliorée**
- **Affichage de l'image actuelle** : Montre l'image existante
- **Upload optionnel** : "Nouvelle image principale (optionnel en édition)"
- **Messages d'aide** : Explications claires pour l'utilisateur

## 🎯 Résultat

### ✅ **Création d'article** :
- Image principale **obligatoire**
- Validation stricte

### ✅ **Édition d'article** :
- Image principale **conservée** si pas de nouveau fichier
- Upload **optionnel** pour remplacer
- Validation intelligente

### ✅ **UX améliorée** :
- Interface claire avec aperçu de l'image actuelle
- Messages d'aide contextuels
- Pas de blocage inattendu

## 🔧 Fichiers modifiés

1. **`src/Entity/Article.php`** :
   - Suppression `@Assert\NotNull` sur `imageMainFile`
   - Ajout méthode `hasMainImage()`
   - Ajout validateur `#[RequiredMainImage]`

2. **`src/Validator/RequiredMainImage.php`** :
   - Contrainte personnalisée

3. **`src/Validator/RequiredMainImageValidator.php`** :
   - Logique de validation intelligente

4. **`src/Controller/Admin/ArticleCrudController.php`** :
   - Interface améliorée avec aperçu images
   - Messages d'aide contextuels

## 🚀 Avantages de la solution

- **Flexibilité** : Création stricte, édition souple
- **UX intuitive** : Pas de surprise pour l'utilisateur
- **Sécurité** : Validation robuste selon le contexte
- **Maintenabilité** : Code propre et logique claire 