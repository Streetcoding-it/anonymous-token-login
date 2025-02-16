Questa struttura e il relativo codice costituiscono un esempio di base:

Al momento della registrazione, il token viene generato e salvato in una tabella personalizzata.
Viene aggiunto un campo di upload nel form di login per consentire all'utente di caricare il file del token.
Viene verificato il token inserito confrontandolo con quello registrato.
È presente anche una route per permettere all'utente di scaricare il token.

Di seguito trovi un documento riepilogativo per il modulo "anonymous-token-login" che include la struttura dei file e la descrizione della funzione di ciascun file, insieme al flusso operativo complessivo:

---

# Documento di Riepilogo: Modulo Anonymous Token Login

## Introduzione

Il modulo "anonymous-token-login" è progettato per implementare un sistema di login basato su token digitale. Durante la registrazione, per ogni nuovo utente viene generato un token univoco che viene memorizzato in una tabella personalizzata. L'utente ha la possibilità di scaricare il token e, durante il login successivo, caricarlo tramite un campo appositamente aggiunto nel form di login. Il modulo verifica quindi che il token caricato corrisponda a quello registrato, consentendo l'autenticazione.

---

## Struttura dei File

La struttura del modulo è organizzata come segue:

```
modules/custom/anonymous-token-login/
├── anonymous_token_login.info.yml
├── anonymous_token_login.install
├── anonymous_token_login.module
├── anonymous_token_login.routing.yml
└── src/
    └── Controller/
        └── TokenDownloadController.php
```

---

## Descrizione dei File

### 1. anonymous_token_login.info.yml

**Funzione:**  
Contiene i metadati e le informazioni di base del modulo. Qui si definiscono il nome del modulo, la descrizione, le versioni compatibili del core (inclusi Drupal 8, 9, 10 e 11), il pacchetto di appartenenza e la versione del modulo.

**Contenuto Esempio:**
```yaml
name: "Anonymous Token Login"
type: module
description: "Modulo personalizzato per implementare un login anonimo tramite token digitale. Alla registrazione viene generato un token che l'utente scarica e poi utilizza per autenticarsi."
core_version_requirement: ^8 || ^9 || ^10 || ^11
package: Custom
version: "1.0"
```

---

### 2. anonymous_token_login.install

**Funzione:**  
Contiene il codice di installazione del modulo. In particolare, implementa `hook_schema()` per creare una tabella personalizzata nel database in cui verrà memorizzato il token associato a ciascun utente.

**Contenuto Esempio:**
```php
<?php

/**
 * @file
 * Install file per il modulo Anonymous Token Login.
 */

/**
 * Implements hook_schema().
 */
function anonymous_token_login_schema() {
  $schema['anonymous_token_login'] = [
    'description' => 'Memorizza il token per il login anonimo degli utenti.',
    'fields' => [
      'uid' => [
        'description' => 'ID dell\'utente.',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
      ],
      'token' => [
        'description' => 'Token univoco generato per il login.',
        'type' => 'varchar',
        'length' => 128,
        'not null' => TRUE,
        'default' => '',
      ],
    ],
    'primary key' => ['uid'],
  ];
  return $schema;
}
```

---

### 3. anonymous_token_login.module

**Funzione:**  
Contiene la logica principale del modulo. Le principali funzioni implementate sono:

- **hook_user_insert():**  
  Viene chiamato al momento della registrazione di un nuovo utente. Qui viene generato un token univoco (ad esempio, utilizzando `random_bytes()`) e salvato nella tabella personalizzata.

- **hook_form_user_login_form_alter():**  
  Modifica il form di login standard di Drupal aggiungendo un campo per il caricamento del file contenente il token.

- **Validatore personalizzato:**  
  Durante il submit del form di login, il validatore confronta il token caricato (dal file) con quello registrato per l'utente, segnalando eventuali errori se non coincidono.

**Contenuto Esempio:**
```php
<?php

/**
 * @file
 * Contiene il codice principale per il modulo Anonymous Token Login.
 */

/**
 * Implements hook_user_insert().
 *
 * Genera un token univoco alla registrazione e lo salva nella tabella custom.
 */
function anonymous_token_login_user_insert(\Drupal\user\UserInterface $account) {
  // Genera un token univoco.
  $token = bin2hex(random_bytes(16));

  // Salva il token nella tabella custom.
  \Drupal::database()->merge('anonymous_token_login')
    ->key(['uid' => $account->id()])
    ->fields(['token' => $token])
    ->execute();
}

/**
 * Implements hook_form_FORM_ID_alter() per alterare il form di login.
 */
function anonymous_token_login_form_user_login_form_alter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) {
  // Aggiunge un campo per l'upload del file contenente il token.
  $form['token_upload'] = [
    '#type' => 'managed_file',
    '#title' => t('File del Token'),
    '#description' => t('Carica il file contenente il token ricevuto durante la registrazione.'),
    '#upload_location' => 'public://token_uploads/',
    '#required' => FALSE,
  ];

  // Aggiunge un validatore personalizzato.
  $form['#validate'][] = 'anonymous_token_login_user_login_form_validate';
}

/**
 * Validatore personalizzato per il form di login.
 *
 * Se viene caricato un file token, lo confronta con quello memorizzato per l'utente.
 */
function anonymous_token_login_user_login_form_validate(&$form, \Drupal\Core\Form\FormStateInterface $form_state) {
  $token_file = $form_state->getValue('token_upload');
  if (!empty($token_file)) {
    // Carica l'oggetto file.
    $file = \Drupal\file\Entity\File::load($token_file[0]);
    if ($file) {
      // Ottiene il percorso reale del file e ne legge il contenuto.
      $uri = $file->getFileUri();
      $real_path = \Drupal::service('file_system')->realpath($uri);
      $token_content = file_get_contents($real_path);

      // Recupera il nome utente inserito nel form.
      $name = $form_state->getValue('name');
      $users = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['name' => $name]);
      $account = reset($users);
      if ($account) {
        // Carica il token memorizzato per l'utente.
        $stored = \Drupal::database()->select('anonymous_token_login', 'atl')
          ->fields('atl', ['token'])
          ->condition('uid', $account->id())
          ->execute()
          ->fetchField();
        if ($stored && trim($stored) !== trim($token_content)) {
          $form_state->setErrorByName('token_upload', t('Il token caricato non è valido.'));
        }
      }
    }
  }
}
```

---

### 4. anonymous_token_login.routing.yml

**Funzione:**  
Definisce una route personalizzata per il download del token. Questa route punta a un controller che si occupa di generare il file contenente il token e di restituirlo in download.

**Contenuto Esempio:**
```yaml
anonymous_token_login.download:
  path: '/anonymous-token-download/{uid}'
  defaults:
    _controller: '\Drupal\anonymous_token_login\Controller\TokenDownloadController::download'
    _title: 'Scarica il Token'
  requirements:
    _permission: 'access content'
    uid: \d+
```

---

### 5. src/Controller/TokenDownloadController.php

**Funzione:**  
Implementa il controller responsabile del download del token. Recupera il token dalla tabella personalizzata, crea un file temporaneo con il contenuto del token e restituisce una risposta di tipo BinaryFileResponse per permettere all'utente di scaricare il file.

**Contenuto Esempio:**
```php
<?php

namespace Drupal\anonymous_token_login\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Controller per il download del token.
 */
class TokenDownloadController extends ControllerBase {

  /**
   * Scarica il file contenente il token.
   *
   * @param int $uid
   *   L'ID dell'utente.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   La risposta HTTP con il file in download.
   */
  public function download($uid) {
    // Recupera il token dalla tabella custom.
    $token = \Drupal::database()->select('anonymous_token_login', 'atl')
      ->fields('atl', ['token'])
      ->condition('uid', $uid)
      ->execute()
      ->fetchField();

    if ($token) {
      // Crea un file temporaneo con il contenuto del token.
      $temp_file = 'temporary://token_' . $uid . '.txt';
      file_put_contents(\Drupal::service('file_system')->realpath($temp_file), $token);

      $response = new BinaryFileResponse(\Drupal::service('file_system')->realpath($temp_file));
      $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'token.txt');
      return $response;
    }
    else {
      return new Response(t('Token non trovato.'), 404);
    }
  }

}
```

---

## Flusso Operativo del Modulo

1. **Registrazione Utente:**  
   - Quando un nuovo utente si registra, `hook_user_insert()` genera un token univoco e lo salva nella tabella `anonymous_token_login`.

2. **Download del Token:**  
   - L'utente può accedere alla route `/anonymous-token-download/{uid}` per scaricare il file contenente il token. Il controller `TokenDownloadController` gestisce il recupero del token e il download.

3. **Login con Token:**  
   - Il form di login standard viene alterato tramite `hook_form_user_login_form_alter()` per includere un campo di upload (managed file) per il token.
   - Durante il submit del form, il validatore personalizzato in `anonymous_token_login.module` confronta il token caricato con quello registrato per l'utente. Se il token non corrisponde, viene generato un errore, altrimenti il login prosegue.

---

## Conclusioni

Questo documento riepiloga la creazione del modulo "anonymous-token-login", includendo:
- La struttura dei file necessaria.
- La funzione e il contenuto di ciascun file.
- Il flusso operativo dal momento della registrazione alla fase di login.

