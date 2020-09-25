<?php

return [
    'title' => 'Boutique',

    'buy' => 'Acheter',

    'month-goal' => 'Objectif du mois',
    'month-goal-current' => ':count% complété|:count% complétés',

    'fields' => [
        'price' => 'Prix',
        'total' => 'Total',
        'quantity' => 'Quantité',
        'currency' => 'Devise',
        'category' => 'Catégorie',
        'package' => 'Produit',
        'packages' => 'Produits',
        'gateways' => 'Moyens de paiements',
        'servers' => 'Serveurs',
        'code' => 'Code',
        'discount' => 'Promotion',
        'start_date' => 'Date de début',
        'expire_date' => 'Date de fin',
        'commands' => 'Commandes',
        'required_packages' => 'Articles pré-requis',
        'user_limit' => 'Limite d\'achats par utilisateur',
    ],

    'actions' => [
        'remove' => 'Retirer',
    ],

    'categories' => [
        'empty' => 'Cette catégorie est vide.',
    ],

    'cart' => [
        'title' => 'Panier',
        'error-money' => 'Vous n\'avez pas assez d\'argent pour faire cet achat.',
        'purchase' => 'Votre achat a été effectué avec succès.',

        'pay-confirm-title' => 'Payer ?',
        'pay-confirm' => 'Êtes vous sûr de vouloir acheter le contenu du panier pour :price ?',

        'guest' => 'Vous devez être connecté pour effectuer un achat.',
        'empty' => 'Votre panier est vide.',

        'checkout' => 'Procéder au paiement',
        'remove' => 'Supprimer',
        'clear' => 'Vider le panier',
        'pay' => 'Payer',

        'coupons' => 'Codes promo',
        'add-coupon' => 'Ajouter un code promo',
        'invalid-coupon' => 'Ce code promo n\'existe pas, est expiré ou ne peut plus être utilisé.',

        'back' => 'Retour à la boutique',

        'total' => 'Total : :total',

        'credit' => 'Créditer',
    ],

    'payment' => [
        'title' => 'Paiement',

        'empty' => 'Aucun moyen de paiement n\'est disponible actuellement.',

        'info' => 'Achat #:id sur :website',
        'error' => 'Le paiement n\'a pas pu être effectué.',

        'success' => 'Paiement effectué',
        'success-info' => 'Vous recevrez votre achat dans le jeu en moins d\'une minute.',

        'redirect-info' => 'Si vous n\'êtes pas redirigé automatiquement, vérifiez que le javascript est activé sur votre navigateur.',

        'webhook' => 'Nouveau paiement effectué sur la boutique',
    ],

    'packages' => [
        'limit' => 'Vous avez acheté le maximum possible pour cet article.',
        'requirements' => 'Vous n\'avez pas acheté les articles nécessaires pour l\'achat de cet article.',
    ],

    'offers' => [
        'title-payment' => 'Moyen de paiement',
        'title-select' => 'Montant',

        'empty' => 'Aucune offre n\'est disponible actuellement.',
    ],
];
