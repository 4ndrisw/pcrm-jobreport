<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['jobreports/jobreport/(:num)/(:any)'] = 'jobreport/index/$1/$2';

/**
 * @deprecated
 */
$route['viewjobreport/(:num)/(:any)'] = 'jobreport/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['jobreports/list'] = 'myjobreport/list';
$route['jobreports/show/(:num)/(:any)'] = 'myjobreport/show/$1/$2';

