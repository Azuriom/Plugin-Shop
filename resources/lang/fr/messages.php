<?php

return [
    'title' => 'Boutique',
    'welcome' => 'Welcome on the shop',

    'buy' => 'Acheter',

    'free' => 'Gratuit',

    'periods' => [
        'hours' => 'heures',
        'days' => 'jours',
        'weeks' => 'semaines',
        'months' => 'mois',
        'years' => 'années',
    ],

    'fields' => [
        'balance' => 'Solde',
        'category' => 'Catégorie',
        'code' => 'Code',
        'commands' => 'Commandes',
        'currency' => 'Devise',
        'discount' => 'Promotion',
        'expire_date' => 'Date de fin',
        'gateways' => 'Moyens de paiements',
        'global_limit' => 'Limite d\'achats globale',
        'original_balance' => 'Solde de départ',
        'package' => 'Produit',
        'packages' => 'Produits',
        'payment_id' => 'ID du Paiement',
        'payments' => 'Paiements',
        'price' => 'Prix',
        'quantity' => 'Quantité',
        'renewal_date' => 'Renouvellement',
        'required' => 'Obligatoire',
        'required_packages' => 'Articles pré-requis',
        'required_roles' => 'Role pré-requis',
        'role' => 'Rôle à définir lors de l\'achat',
        'start_date' => 'Date de début',
        'subscription' => 'Abonnement',
        'subscription_id' => 'ID d\'abonnement',
        'total' => 'Total',
        'user_id' => 'ID de l\'utilisateur',
        'user_limit' => 'Limite d\'achats par utilisateur',
    ],

    'actions' => [
        'subscribe' => 'S\'abonner',
        'manage' => 'Gérer l\'abonnement',
        'move' => 'Déplacer',
    ],

    'goal' => [
        'title' => 'Objectif du mois',
        'progress' => ':count% complété|:count% complétés',
    ],

    'recent' => [
        'title' => 'Paiements récents',
        'empty' => 'Pas de paiements récents',
    ],

    'top' => [
        'title' => 'Meilleur acheteur',
    ],

    'cart' => [
        'title' => 'Panier',
        'success' => 'Votre achat a été effectué avec succès.',
        'guest' => 'Vous devez être connecté pour effectuer un achat.',
        'empty' => 'Votre panier est vide.',
        'checkout' => 'Procéder au paiement',
        'clear' => 'Vider le panier',
        'pay' => 'Payer',
        'back' => 'Retour à la boutique',
        'total' => 'Total : :total',
        'payable_total' => 'Total à payer: :total',
        'credit' => 'Créditer',

        'confirm' => [
            'title' => 'Payer ?',
            'price' => 'Êtes vous sûr de vouloir acheter le contenu du panier pour :price ?',
        ],

        'errors' => [
            'money' => 'Vous n\'avez pas assez d\'argent pour faire cet achat.',
            'execute' => 'Une erreur est survenue lors du paiement, votre achat a été remboursé.',
        ],
    ],

    'coupons' => [
        'title' => 'Codes promo',
        'add' => 'Ajouter un code promo',
        'error' => 'Ce code promo n\'existe pas, est expiré ou ne peut plus être utilisé.',
        'cumulate' => 'Ce code promo ne peut pas être utilisé avec un autre code promo.',
    ],

    'payment' => [
        'title' => 'Paiement',
        'manual' => 'Paiement manuel',

        'empty' => 'Aucun moyen de paiement n\'est disponible actuellement.',

        'info' => 'Achat #:id sur :website',
        'subscription' => ':package - Abonnement (Utilisateur #:user)',
        'error' => 'Le paiement n\'a pas pu être effectué, veuillez réessayer.',
        'giftcards' => 'Cartes cadeaux',

        'success' => 'Paiement effectué, vous recevrez votre achat dans le jeu en moins d\'une minute',
        'pending' => 'Paiement en attente, vous recevrez votre achat dans le jeu une fois le paiement confirmé',

        'webhook' => 'Nouveau paiement effectué sur la boutique',
        'webhook_info' => 'Total : :total, ID : :id (:gateway)',
        'webhook_chargeback' => 'Litige effectué sur la boutique',
        'webhook_refund' => 'Remboursement effectué sur la boutique',
    ],

    'categories' => [
        'empty' => 'Cette catégorie est vide.',
    ],

    'packages' => [
        'limit' => 'Vous avez acheté le maximum possible pour cet article.',
        'requirements' => 'Vous n\'avez pas les pré-requis pour acheter cet article.',
        'cumulate' => 'Vous ne pouvez pas acheter ce produit avec un autre de la même catégorie dans le même achat.',
        'file' => 'Cliquez ici pour télécharger le fichier :file.',
    ],

    'offers' => [
        'gateway' => 'Moyen de paiement',
        'amount' => 'Montant',

        'empty' => 'Aucune offre n\'est disponible actuellement.',
    ],

    'profile' => [
        'payments' => 'Vos achats',
        'subscriptions' => 'Vos abonnements',
        'money' => 'Argent : :balance',
    ],

    'giftcards' => [
        'title' => 'Cartes cadeaux',
        'error' => 'Cette carte cadeau n\'existe pas, est expirée ou ne peut plus être utilisée.',
        'add' => 'Utiliser une carte cadeau',
        'notification' => 'Vous avez reçu une carte cadeau, son code est : :code (:balance).',
        'pending' => 'Un paiement est déjà en cours pour cette carte cadeau. Complétez le paiement ou attendez un peu.',
    ],
];
