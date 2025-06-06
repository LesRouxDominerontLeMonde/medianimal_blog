# Structure des Images et Publication d'Articles

## Système de Publication

### Champs de contrôle de publication

| Champ | Rôle | Type |
|-------|------|------|
| `createdAt` | Quand l'article a été créé | DateTimeImmutable |
| `publishedAt` | Date effective de publication (affichée publiquement) | DateTimeImmutable (nullable) |
| `isPublished` | Est-ce que l'article est visible/public/indexable ? | Boolean |

### Avantages du système :
- ✅ **Création en brouillon** : Tu peux créer ton article, uploader l'image, faire des vérifications
- ✅ **Planification** : Tu peux donner n'importe quelle date à `publishedAt` (dans le passé ou futur)
- ✅ **Contrôle précis** : Tu contrôles précisément quand l'article devient visible

### Logique de visibilité
Un article est visible publiquement si :
1. `isPublished = true` ET
2. `publishedAt` est défini ET
3. `publishedAt <= maintenant`

## Champs d'images

### Image principale (`imageMainPath`)
- **Obligatoire** : Oui
- **Utilisation** : Image principale illustrant l'article
- **Contraintes** :
  - Formats : JPEG, PNG, WebP
  - Taille max : 2 Mo
  - Résolution min : 50 000 pixels
  - Message d'erreur : "Image trop lourde, max 2 Mo"

### Image alternative (`imageAltPath`)
- **Obligatoire** : Non
- **Utilisation** : Image secondaire, autre angle ou photo complémentaire
- **Contraintes** : Identiques à l'image principale

## Stockage
- **Dossier** : `public/images/articles/`
- **Nommage** : SmartUniqueNamer (VichUploader)
- **Accès public** : Oui (pour SEO et référencement Google)

## Structure JSON-LD pour SEO

```json
"image": [
  "{{ asset(article.imageMainPath) | imagine_filter('article_16x9') }}",
  "{{ asset(article.imageMainPath) | imagine_filter('article_4x3') }}",
  "{{ asset(article.imageMainPath) | imagine_filter('article_1x1') }}",
  {% if article.imageAltPath %}
    "{{ asset(article.imageAltPath) | imagine_filter('article_16x9') }}",
    "{{ asset(article.imageAltPath) | imagine_filter('article_4x3') }}",
    "{{ asset(article.imageAltPath) | imagine_filter('article_1x1') }}"
  {% endif %}
]
```

## Avantages SEO
- Multiple formats d'images pour Google
- Images publiquement accessibles
- Différents ratios (16:9, 4:3, 1:1) via LiipImagineBundle
- Image alternative pour plus de choix
- Contrôle précis de la publication pour l'indexation

## Configuration VichUploader
```yaml
vich_uploader:
    db_driver: orm
    mappings:
        article_images:
            uri_prefix: /images/articles
            upload_destination: '%kernel.project_dir%/public/images/articles'
            namer: Vich\UploaderBundle\Naming\SmartUniqueNamer
```

## Entité Article - Structure finale

### Champs de base
- `id` : Identifiant unique (int, auto-increment)
- `title` : Titre de l'article (string, NOT NULL)
- `content` : Contenu de l'article (TEXT, NOT NULL)

### Champs de publication
- `createdAt` : Date de création (DateTimeImmutable, NOT NULL)
- `publishedAt` : Date de publication (DateTimeImmutable, nullable)
- `isPublished` : Article publié (boolean, default false)

### Champs d'images
- `imageMainPath` : Chemin de l'image principale (string, NOT NULL)
- `imageMainFile` : Fichier upload image principale (File, avec contraintes)
- `imageAltPath` : Chemin de l'image alternative (string, nullable)
- `imageAltFile` : Fichier upload image alternative (File, avec contraintes)

## Méthodes utiles
- `isVisible()` : Vérifie si l'article est visible publiquement
- `isPublished()` : Getter pour le statut de publication
- `setIsPublished(bool)` : Setter pour contrôler la publication
- `getImageMainPath()` / `setImageMainPath()` : Gestion image principale
- `getImageAltPath()` / `setImageAltPath()` : Gestion image alternative

## Migrations appliquées
- ✅ Ajout des champs `imageMainPath` et `imageAltPath`
- ✅ Ajout du champ `isPublished`
- ✅ Suppression de l'ancien champ `image` (obsolète) 