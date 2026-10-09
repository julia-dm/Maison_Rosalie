<?php
//Maison_Rosalie/config-dev.php
const DB_HOST = "localhost";
const DB_LOGIN = "root";
const DB_PWD = "";
const DB_NAME = "maison_rosalie";
const DB_PORT = 3306;
const DB_CHARSET = "utf8mb4";

const DB_TYPE = "mysql";

// Symfony Mailer (Gmail) : adresse d'envoi + mot de passe d'application Google
const MAILER_EMAIL = "";
const MAILER_APP_PASSWORD = "";
// facultatif : destinataire des messages du formulaire de contact (par défaut MAILER_EMAIL)
// const CONTACT_EMAIL = "contact@maisonrosalie.be";
// facultatif : autre serveur SMTP que Gmail, ex. "smtp://user:pass@smtp.exemple.com:587"
// const MAILER_DSN = "";

// racine de notre site pour PHP
const RACINE_PATH = __DIR__;
