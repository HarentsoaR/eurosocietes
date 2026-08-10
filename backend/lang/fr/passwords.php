<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Password Reset Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the password broker for a password update attempt
    | has failed, such as for an invalid token or invalid new password.
    |
    */

    'reset' => 'Votre mot de passe a été réinitialisé.',
    'sent' => 'Nous vous avons envoyé par e-mail le lien de réinitialisation du mot de passe.',
    'throttled' => 'Veuillez patienter avant de réessayer.',
    'token' => 'Ce jeton de réinitialisation du mot de passe est invalide.',
    'user' => 'Aucun utilisateur n\'a été trouvé avec cette adresse e-mail.',
    'failed' => 'La réinitialisation du mot de passe a échoué. Vérifiez vos informations et réessayez.',

    // Password reset email (notification content)
    'email_subject' => 'Réinitialisation de votre mot de passe',
    'email_intro' => 'Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.',
    'email_token' => 'Votre code de réinitialisation est :',
    'email_expires' => 'Ce code de réinitialisation expirera dans :count minutes.',
    'email_ignore' => 'Si vous n\'avez pas demandé de réinitialisation de mot de passe, aucune action n\'est requise.',

];
