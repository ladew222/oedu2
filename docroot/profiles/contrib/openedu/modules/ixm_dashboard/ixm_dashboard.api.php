<?php

/**
 * @file
 * Hooks provided by the ixm_dashboard module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Add items to the ixm_dashboard exclusions.
 *
 * The IXM Dashboard will not show on any paths included in the list of
 * exclusions.
 *
 * This hook is invoked in ixm_dashboard_exluded_paths().
 *
 * @return array
 *   An array of path aliases or paths.
 *
 * @see ixm_dashboard_exluded_paths()
 * @ingroup ixm_dashboard
 */
function hook_ixm_dashboard_exlusion() {
  return [
    'node/*/latest',
    'custom-page',
  ];
}

/**
 * Alter the exclusions after hook_ixm_dashboard_exlusion() is invoked.
 *
 * @param array $exclusions
 *   Associative array of exclusions hook_toolbar().
 */
function hook_ixm_dashboard_exlusion_alter(array &$exclusions) {
  $some_condition = TRUE;

  // Allow the dashboard under certain conditions.
  if ($some_condition) {
    unset($exclusions['custom-page']);
  }
}

/**
 * @} End of "addtogroup hooks".
 */
