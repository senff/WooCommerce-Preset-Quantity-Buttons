<?php

defined('WP_UNINSTALL_PLUGIN') or die('INSERT COIN');

// Remove all per-product meta
delete_post_meta_by_key('_pqb_quantities');
delete_post_meta_by_key('_pqb_hide_standard');
delete_post_meta_by_key('_pqb_button_prefix');
delete_post_meta_by_key('_pqb_button_suffix');

// Remove plugin options
delete_option('pqb_global_quantities');
delete_option('pqb_global_hide_standard');
delete_option('pqb_button_prefix');
delete_option('pqb_button_suffix');
delete_option('pqb_apply_to_all');
