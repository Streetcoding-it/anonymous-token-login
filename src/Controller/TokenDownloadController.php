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
