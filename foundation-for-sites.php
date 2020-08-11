<?php
namespace Grav\Plugin;

use \Grav\Common\Plugin;
use \Grav\Common\Grav;
use \Grav\Common\Page\Page;

class FoundationForSitesPlugin extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onThemeInitialized' => ['onThemeInitialized', 0]
        ];
    }

    /**
     * Initialize configuration
     */
    public function onThemeInitialized()
    {
        if ($this->isAdmin()) {
            return;
        }

        $load_events = false;

        // if not always_load see if the theme expects to load foundation-for-sites plugin
        if (!$this->config->get('plugins.foundation-for-sites.always_load')) {
            $theme = $this->grav['theme'];
            if (isset($theme->load_foundation_for_sites_plugin) && $theme->load_foundation_for_sites_plugin) {
                $load_events = true;
            }
        } else {
            $load_events = true;
        }

        if ($load_events) {
            $this->enable([
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
            ]);
        }
    }

    /**
     * if enabled on this page, load the JS + CSS and set the selectors.
     */
    public function onTwigSiteVariables()
    {
        $config = $this->config->get('plugins.foundation-for-sites');
        $version = $config['version'];
        $mode = $config['mode'] == 'production' ? '.min' : '';
        $jQuery = "3.5.1";

        $foundation_bits = [];

        $assets = $this->grav['assets'];

        if ($version == 'v6.6') {
            $currentVersion = '6.6.3';
            $foundationCDN = 'https://cdn.jsdelivr.net/npm/foundation-sites@';
        } else {
            $currentVersion = '6.5.3';
            $foundationCDN = 'https://cdn.jsdelivr.net/npm/foundation-sites@';
        }

        if ($config['use_cdn']) {
            if ( $config['one_file_css'] ) {
                $foundation_bits[] = "{$foundationCDN}{$currentVersion}/dist/css/foundation{$mode}.css";
            } else {
                if ( $config['foundation_float_css'] ) {
                    $foundation_bits[] = "{$foundationCDN}{$currentVersion}/dist/css/foundation-float{$mode}.css";
                }
                if ( $config['foundation_prototype_css'] ) {
                    $foundation_bits[] = "{$foundationCDN}{$currentVersion}/dist/css/foundation-prototype{$mode}.css";
                }
                if ( $config['foundation_rtl_css'] ) {
                    $foundation_bits[] = "{$foundationCDN}{$currentVersion}/dist/css/foundation-rtl{$mode}.css";
                }
            }

            // Load jQuery before Foundation
            $assets->addJs("https://code.jquery.com/jquery-{$jQuery}{$mode}.js", ['priority' => 101, 'group' => 'bottom']);
            
            if ( $config['one_file_js'] ) {
                $assets->addJs("{$foundationCDN}{$currentVersion}/dist/js/foundation{$mode}.js", ['group' => 'bottom']);
            } else {
                // Read js/plugins & load scripts
                if ( $handle = opendir("plugin://{$this->name}/js/{$version}/plugins/") ) {

                    while ( $entry = readdir($handle) ) {
                        if (strpos($entry, '.min.js') && !strpos($entry, '.map')) {
                            $prefix = "foundation.";
                            $full_name = basename($entry, '.min.js'); // eg: foundation.util.nest
                            $no_prefix = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $full_name); // eg: util.nest
                            $no_dot = preg_replace('/\./', '_', $no_prefix); // eg: util_nest
                            
                            if ( $config['foundation_' . $no_dot . '_js']) {
                                if ( $no_prefix == "core" ) {
                                    $assets->addJs("{$foundationCDN}{$currentVersion}/dist/js/plugins/foundation.{$no_prefix}{$mode}.js", ['priority' => 100, 'group' => 'bottom']);
                                } elseif ( $no_prefix == "accordion" ) {
                                    $assets->addJs("{$foundationCDN}{$currentVersion}/dist/js/plugins/foundation.{$no_prefix}{$mode}.js", ['priority' => 99, 'group' => 'bottom']);
                                } else {
                                    $assets->addJs("{$foundationCDN}{$currentVersion}/dist/js/plugins/foundation.{$no_prefix}{$mode}.js", ['group' => 'bottom']);
                                }
                            }
                        }
                    }
                    
                    closedir($handle);
                }
            }
        } else {
            if ( $config['one_file_css'] ) {
                $foundation_bits[] = "plugin://{$this->name}/css/{$version}/foundation{$mode}.css";
            } else {
                if ( $config['foundation_float_css'] ) {
                    $foundation_bits[] = "plugin://{$this->name}/css/{$version}/foundation-float{$mode}.css";
                }
                if ( $config['foundation_prototype_css'] ) {
                    $foundation_bits[] = "plugin://{$this->name}/css/{$version}/foundation-prototype{$mode}.css";
                }
                if ( $config['foundation_rtl_css'] ) {
                    $foundation_bits[] = "plugin://{$this->name}/css/{$version}/foundation-rtl{$mode}.css";
                }
            }
            
            // Load jQuery before Foundation
            $assets->addJs("plugin://{$this->name}/js/jquery/jquery-{$jQuery}{$mode}.js", ['priority' => 101, 'group' => 'bottom']);

            if ( $config['one_file_js'] ) {
                $assets->addJs("plugin://{$this->name}/js/{$version}/foundation{$mode}.js", ['group' => 'bottom']);
            } else {
                // Read js/plugins & load scripts
                if ( $handle = opendir("plugin://{$this->name}/js/{$version}/plugins/") ) {

                    while ( $entry = readdir($handle) ) {
                        if (strpos($entry, '.min.js') && !strpos($entry, '.map')) {
                            $prefix = "foundation.";
                            $full_name = basename($entry, '.min.js'); // eg: foundation.util.nest
                            $no_prefix = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $full_name); // eg: util.nest
                            $no_dot = preg_replace('/\./', '_', $no_prefix); // eg: util_nest
                            
                            if ( $config['foundation_' . $no_dot . '_js']) {
                                if ( $no_prefix == "core" ) {
                                    $assets->addJs("plugin://{$this->name}/js/{$version}/plugins/foundation.{$no_prefix}{$mode}.js", ['priority' => 100, 'group' => 'bottom']);
                                } elseif ( $no_prefix == "accordion" ) {
                                    $assets->addJs("plugin://{$this->name}/js/{$version}/plugins/foundation.{$no_prefix}{$mode}.js", ['priority' => 99, 'group' => 'bottom']);
                                } else {
                                    $assets->addJs("plugin://{$this->name}/js/{$version}/plugins/foundation.{$no_prefix}{$mode}.js", ['group' => 'bottom']);
                                }    
                            }
                        }
                    }
                    
                    closedir($handle);
                }
            }
        }

        $assets->registerCollection('foundation-for-sites', $foundation_bits);
        $assets->addInlineJs('$(document).foundation();', ['group' => 'bottom']); // Initializing all Foundation plugin at once.
        $assets->add('foundation-for-sites', 100);
    }
}
