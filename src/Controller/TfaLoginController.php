<?php

namespace Drupal\tfa\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Component\Utility\Crypt;
use Drupal\user\Entity\User;

/**
 * Provides access control on the verification form.
 *
 * @package Drupal\tfa\Controller
 */
class TfaLoginController {

  /**
   * Denies access unless user matches hash value.
   *
   * @param \Drupal\Core\Routing\RouteMatch $route
   *   The route to be checked.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(RouteMatch $route) {
    $user = $route->getParameter('user');
    $condition = is_object($user) && ($user instanceof User);
    return AccessResult::allowedIf($condition && ($this->getLoginHash($user) == $route->getParameter('hash')));
  }

  /**
   * Copied from TfaLoginForm.php.
   *
   * @param \Drupal\user\Entity\User $account
   *   The user account for which a hash is required.
   *
   * @return string
   *   The hash value representing the user.
   */
  protected function getLoginHash(User $account) {
    // Using account login will mean this hash will become invalid once user has
    // authenticated via TFA.
    $data = implode(':', [
      $account->getUsername(),
      $account->getPassword(),
      $account->getLastLoginTime(),
    ]);
    return Crypt::hashBase64($data);
  }

}
