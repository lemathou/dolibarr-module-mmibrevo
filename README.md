# MMIBrevo — Brevo : campagnes e-mail/SMS, contacts et statistiques.

> Module Dolibarr **MMIBrevo** (version 1.1), branche `20.0`.

## Fonctionnalités

### Fonctionnalités principales

- Campagnes e-mail/SMS, contacts et statistiques Brevo.
- Configuration API Brevo.

### Pages

- `admin/about.php`
- `admin/setup.php`
- `mmibrevoindex.php`
- `sms_invoice.php`
- `sms_order.php`
- `sms_propal.php`
- `sms_send.php`
- `sms_thirdparty.php`

### Permissions

- `mmibrevo.send.read` — Send SMS with Brevo

### Configuration

| Constante | Type | Rôle |
|---|---|---|
| `MMI_BREVO_API_URL` | string | URL API Brevo |
| `MMI_BREVO_API_KEY` | securekey | Clé API Brevo |
| `MMI_BREVO_API_FROM` | string | Expéditeur Brevo |
| `MMI_BREVO_API_ORGANISATION_PREFIX` | string |  |

### Hooks

- Contextes : `propalcard`, `ordercard`, `invoicecard`

### Classes et composants

- `class/actions_mmibrevo.class.php`
- `class/mmi_brevo.class.php`
- `class/mmi_brevo_sms.class.php`
- `lib/mmibrevo.lib.php`

## Prérequis

- Dolibarr >= 11.0 (branche `20.0` : Dolibarr 20.0).
- Modules MMI : `mmicommon`. **`mmicommon` doit être activé en premier.**

## Installation

1. Copier le module dans `htdocs/custom/mmibrevo` :

   ```sh
   cd htdocs/custom
   git clone <dépôt> mmibrevo
   ```

2. Dans Dolibarr : **Configuration → Modules**, activer d'abord `mmicommon`, puis ce module.
3. Vérifier les permissions dans la fiche des utilisateurs concernés.

## Licence

GPLv3 ou ultérieure — voir `COPYING`. Documentation sous GFDL.

## Changelog

Voir [CHANGELOG.md](CHANGELOG.md) (évolutions datées).
