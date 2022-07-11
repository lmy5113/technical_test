# Test technique GSOi

Ce test a pour but d’évaluer vos compétences en **PHP** et **Symfony**.
Il ne sera pas utilisé par GSOi.

_**Temps estimé** : entre 3 et 4 heures_

_**Note** : Si vous n'avez pas le temps de finir, ajoutez des TODO explicites avec des explications
sur comment vous auriez implémenté la partie manquante.
Privilégiez la conception et l'implémentation sur la documentation._

## Énoncé

On voudrait créer une application de gestion de bookmarks, qui permet d'ajouter différents types de
liens (vidéo, musique, image, gif...).

Chaque type de lien peut avoir plusieurs providers (Vimeo, Youtube, Flickr, etc...).

L'application sera **évolutive** et **resiliente**.

## Instructions

Pour le test vous devrez implémenter **une API REST au format JSON** qui permet de:
* Lister les bookmarks
* Ajouter un bookmark
* Supprimer un bookmark

Votre application devra gérer à minima les providers suivants :
* Flickr
* Vimeo

A minima, un bookmark doit avoir les propriétés suivantes:
* URL
* Nom du provider
* Titre
* Auteur
* Date d'ajout dans l'application que vous développez
* Date de publication

Les bookmarks de type vidéo auront les propriétés additionelles suivantes :
* largeur
* hauteur
* durée

Idem pour les bookmarks de type image :
* largeur
* hauteur


La récupération des propriétés d’un bookmark référencé se fait en utilisant le protocole ouvert [oEmbed](http://oembed.com/).
_Exemple de librairie qui implémente oembed: https://github.com/oscarotero/Embed_.

**L'Application front qui consomme l'API n'a pas besoin de la date de publication.**

## Contraintes

* Utiliser **Symfony 6.x** et **PHP 8+**
* Ne pas utiliser de générateur d'API tel que **API Platform**, **Fos RestBundle**, etc...
* Pas besoin de faire la partie front qui consomme l'API.

Le livrable attendu est une archive de l’application incluant si besoin les instructions d’installation.

## Info supplémentaire

Pour vérifier votre application, vous pouvez vous aider des tests fonctionnels déjà mis en place : 

```
make test-functional
```

## Aide

### oEmbed

Pour récupérer les données vimeo et flickr, si vous utilisez la bibliothèque proposée :

Vous pouvez récupérer les infos via : `$info = $this->embed->get($url)`.
Vous pouvez récupérer les champs spécifiques via `$info->getOEmbed()`.

### Mysql

http://127.0.0.1:9000

```
username: root
password: 123
```
