<?php

class Keitaro_Admin {
    private $plugin_name;
    private $version;
    private $hook_suffix;
    private $main_settings_group;
    private $page_settings_group;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function add_settings_page() {
        #add_options_page('Keitaro Options', 'Keitaro Options', 'manage_options', 'keitaro-admin', array($this, 'create_settings_page'));
    }

    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/keitaro-admin.css', array(), $this->version, 'all' );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/keitaro-admin.js', array( 'jquery' ), $this->version, false );
    }

    public function admin_menu() {
        $this->hook_suffix = add_menu_page(
            $this->settings_page_name(),
            'Keitaro',
            'manage_options',
            $this->plugin_name,
            array($this, 'show_settings_page'),
            'none'
        );
        $this->main_settings_group = $this->hook_suffix . '_main';
        $this->page_settings_group = $this->hook_suffix . '_pages';
    }

    public function show_settings_page() {
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'main';

        echo '<div class="wrap">';
        echo '<header><h1>' . esc_html( $this->settings_page_name() ) . '</h1></header>';
        settings_errors( );

        echo '<h2 class="nav-tab-wrapper">
            <a href="?page=keitaro&tab=main" class="nav-tab ' . ($active_tab == 'main' ? 'nav-tab-active' : '') . '">' . __('Main Settings', $this->plugin_name) . '</a>
            <a href="?page=keitaro&tab=pages" class="nav-tab ' . ($active_tab == 'pages' ? 'nav-tab-active' : '') . '">' . __('Page Settings', $this->plugin_name) . '</a>
        </h2>';

        echo '<form id="keitaro-settings-form" method="post" action="options.php">';

        if ( $active_tab === 'main' ) {
            settings_fields($this->main_settings_group);
            do_settings_sections($this->main_settings_group);
        } else {
            settings_fields($this->hook_suffix . '_pages');
            do_settings_sections($this->hook_suffix . '_pages');
        }
        submit_button();

        echo '</form>';
        echo '</div>';
    }

    public function admin_init() {
        register_setting($this->main_settings_group, 'keitaro_settings');
        register_setting($this->page_settings_group, 'keitaro_page_settings');

        $this->init_main_settings($this->main_settings_group);
        $this->init_page_settings($this->page_settings_group);
    }

    public function init_main_settings($group) {
        $settings = (array) get_option('keitaro_settings');
        $section = 'keitaro_main_section';
        $yesNoOptions = array(
            array('name' => __('Yes', $this->plugin_name), 'value' => 'yes'),
            array('name' => __('No', $this->plugin_name), 'value' => 'no'),
        );

        add_settings_section(
            $section,
            __('Main', $this->plugin_name),
            null,
            $group
        );


        add_settings_field(
            'import',
            '',
            array($this, 'import_settings'),
            $group,
            $section
        );

        add_settings_field(
            'enabled',
            __('Enabled', $this->plugin_name),
            array($this, 'radio_buttons'),
            $group,
            $section, array(
                'name' => 'keitaro_settings[enabled]',
                'value' => isset($settings['enabled']) ? $settings['enabled'] : 'no',
                'options' => $yesNoOptions,
                'description' => __('Choose "no" to disable Keitaro', $this->plugin_name),
            )
        );

        add_settings_field(
            'tracker_url',
            __('Tracker URL', $this->plugin_name),
            array($this, 'text_input'),
            $group,
            $section, array(
                'name' => 'keitaro_settings[tracker_url]',
                'value' => $settings['tracker_url'],
                'size' => 100,
                'placeholder' => 'http://your-tracker.com/',
                'description' => __('Where Keitaro installed', $this->plugin_name),
            )
        );

        add_settings_field(
            'postback_url',
            __('Postback URL', $this->plugin_name),
            array($this, 'text_input'),
            $group,
            $section, array(
                'name' => 'keitaro_settings[postback_url]',
                'value' => $settings['postback_url'],
                'size' => 100,
                'placeholder' => 'http://your-tracker.com/123/postback',
                'description' => __('Where to send postbacks', $this->plugin_name),
            )
        );

        add_settings_field(
            'token',
            __('Primary Campaign Token', $this->plugin_name),
            array($this, 'text_input'),
            $group,
            $section, array(
                'name' => 'keitaro_settings[token]',
                'value' => $settings['token'],
                'size' => 35,
                'description' => __('Enter the token of your primary campaign from the campaign settings. You can call another campaign in the Pages Settings.', $this->plugin_name)
            )
        );

        add_settings_field(
            'use_title_as_keyword',
            __('Use post title as keyword', $this->plugin_name),
            array($this, 'radio_buttons'),
            $group,
            $section, array(
                'name' => 'keitaro_settings[use_title_as_keyword]',
                'value' => isset($settings['use_title_as_keyword']) ? $settings['use_title_as_keyword'] : 'no',
                'options' => $yesNoOptions,
                'description' => __('Choose \'yes\' in order to use post title as keyword', $this->plugin_name),
            )
        );

        add_settings_field(
            'track_hits',
            __('Track non-unique visits', $this->plugin_name),
            array($this, 'radio_buttons'),
            $group,
            $section, array(
                'name' => 'keitaro_settings[track_hits]',
                'value' => isset($settings['track_hits']) ? $settings['track_hits'] : 'yes',
                'options' => $yesNoOptions
            )
        );

        add_settings_field(
            'force_redirect_offer',
            __('Force redirect to offer', $this->plugin_name),
            array($this, 'radio_buttons'),
            $group,
            $section, array(
                'name' => 'keitaro_settings[force_redirect_offer]',
                'value' => isset($settings['force_redirect_offer']) ? $settings['force_redirect_offer'] : 'no',
                'options' => $yesNoOptions,
                'description' => __('Choose \'yes\' in order to perform forced redirects to offers, \'no\' if to use links to the offers.', $this->plugin_name),
            )
        );

        add_settings_field(
            'debug',
            __('Debug enabled', $this->plugin_name),
            array($this, 'radio_buttons'),
            $group,
            $section, array(
                'name' => 'keitaro_settings[debug]',
                'value' => isset($settings['debug']) ? $settings['debug'] : 'no',
                'options' => $yesNoOptions,
                'description' => __('You\'ll see request and response to Click API on all pages', $this->plugin_name),

            )
        );

        add_settings_field(
            'disable_sessions',
            __('Disable session cookies', $this->plugin_name),
            array($this, 'radio_buttons'),
            $group,
            $section, array(
                'name' => 'keitaro_settings[disable_sessions]',
                'value' => isset($settings['disable_sessions']) ? $settings['disable_sessions'] : 'no',
                'options' => $yesNoOptions,
                'description' => __('Without this cookie restoreFromSession wouldn\'t work', $this->plugin_name),

            )
        );
    }

    private function init_page_settings($group) {
        $settings = (array) get_option('keitaro_page_settings');
        $section = 'keitaro_main_section';

        add_settings_section(
            $section,
            '',
            null,
            $group
        );

        $options = array(
            array('name' => __('Call primary campaign on every page', $this->plugin_name), 'value' => 'no'),
            array('name' => __('Specify manually', $this->plugin_name), 'value' => 'yes'),
        );

        add_settings_field(
            'specify_pages',
            __('Choose campaigns', $this->plugin_name),
            array($this, 'radio_buttons'),
            $group,
            $section, array(
                'name' => 'keitaro_page_settings[specify_pages]',
                'value' => isset($settings['specify_pages']) ? $settings['specify_pages'] : 'no',
                'options' => $options,
            )
        );

        add_settings_field(
            'pages',
            __('Pages'),
            array($this, 'pages_table'),
            $group,
            $section,
            array(
                'name' => 'keitaro_page_settings[pages]',
                'selected' => @$settings['pages']
            )
        );

    }

    public function pages_table($args) {
        echo '<div class="keitaro-pages">';

        echo '<div class="keitaro-pages-page keitaro-pages-header">';
        echo '<div class="keitaro-pages-page-item keitaro-pages-page-name">' . __('Page Name', $this->plugin_name) . '</div>';
        echo '<div class="keitaro-pages-page-item keitaro-pages-page-campaign">' . __('Campaign', $this->plugin_name) . '</div>';
        echo '</div>';


        $pages = get_pages(array(
            'sort_order' => 'asc',
            'sort_column' => 'post_title',
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'meta_key' => '',
            'meta_value' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => -1,
            'exclude_tree' => '',
            'number' => '',
            'offset' => 0,
            'post_type' => 'page',
            'post_status' =>  'publish,draft'
        ));

        foreach ($pages as $page) {
            echo '<div class="keitaro-pages-page">';

            echo '<div class="keitaro-pages-page-item keitaro-pages-page-name">' . $page->post_title . '</div>';
            echo '<div class="keitaro-pages-page-item keitaro-pages-page-campaign">';
            $name = $args['name'] . '[' . $page->ID . ']';
            $value = @$args['selected'][$page->ID];
            if ($value && $value !='primary_campaign') {
                $selectedValue = 'another';
            } else {
                $selectedValue = $value;
            }
            echo $this->get_campaign_selector(
                $name,
                $selectedValue
            );

            $this->text_input(array(
                'name' => $name,
                'value' => $value,
                'class' => 'keitaro-pages-page-token keitaro-hidden',
                'placeholder' => __('Campaign Token', $this->plugin_name),
                'size' => 30
            ));

            echo '</div>';

            echo '</div>';
        }



        echo '</div>';
    }

    private function get_campaign_selector($name, $selectedValue) {
        $options = array(
            array('name' => __('Do Nothing', $this->plugin_name), 'value' => ''),
            array('name' => __('Primary Campaign', $this->plugin_name), 'value' => 'primary_campaign'),
            array('name' => __('Another', $this->plugin_name), 'value' => 'another'),
        );

        $this->select(array(
            'name' => $name,
            'options' => $options,
            'selected' => $selectedValue
        ));

    }

    private function settings_page_name() {
        return __( 'Keitaro Settings', $this->plugin_name);
    }

    function text_input($args) {
        $name = esc_attr($args['name']);
        $class = isset($args['class']) ? $args['class'] : "";
        $value = esc_attr($args['value']);
        $size = esc_attr($args['size']);
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : "";
        $description = isset($args['description']) ? $args['description'] : "";

        echo "<input class='$class' type='text' name='$name' size='$size' value='$value' placeholder='$placeholder' />";
        if (!empty($description)) {
            echo '<p class="description">';
            echo esc_html($description);
            echo '</p>';
        }
    }

    function radio_buttons($args) {
        $name = esc_attr($args['name']);
        $value = esc_attr($args['value']);
        $options = $args['options'];
        $description = isset($args['description']) ? $args['description'] : "";

        foreach ($options as $i => $option) {
            if ($option['value'] == $value) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
            echo "<label for='$name-$i'>
            <input type='radio' name='$name' id='$name-$i' value='{$option['value']}' $checked>
                {$option['name']}
            </label>&nbsp;&nbsp;";
        }
        if (!empty($description)) {
            echo '<p class="description">';
            echo esc_html($description);
            echo '</p>';
        }
    }

    function select($args) {
        $name = esc_attr($args['name']);
        $selected = esc_attr($args['selected']);
        $options = $args['options'];
        $description = isset($args['description']) ? $args['description'] : "";
        echo "<select name=\"{$name}\">";
        foreach ($options as $i => $option) {
            if ($option['value'] === $selected) {
                $isSelected = 'selected';
            } else {
                $isSelected = '';
            }
            echo "<option value=\"{$option['value']}\" {$isSelected}>{$option['name']}</option>";
        }
        echo "</select>";
        if (!empty($description)) {
            echo '<p class="description">';
            echo esc_html($description);
            echo '</p>';
        }
    }

    public function import_settings()
    {
        echo '<div id="keitaro-import-success" style="display:none" class="updated settings-error notice">' .
            __('Settings successfully imported', $this->plugin_name)
            . '</div>';

        echo '<a href="#" id="keitaro-import-settings" class="button">' .
            __('Import settings', $this->plugin_name)
        . '</a>';
        echo '<textarea id="keitaro-import-box" style="display:none" rows="10"></textarea>';
        echo '<p><a href="#" id="keitaro-import-button" class="button button-primary" style="display:none">' .
            __('Import', $this->plugin_name)
            . '</a></p>';

    }

    public function plugin_links( $links, $plugin_file, $plugin_data ) {
        if ( isset( $plugin_data['PluginURI'] ) && false !== strpos( $plugin_data['PluginURI'], 'keitarotds.com' ) ) {
            $slug = basename( $plugin_data['PluginURI'] );
            $links[] = sprintf( '<a href="%s" class="thickbox" title="%s">%s</a>',
                self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . $slug . '&amp;TB_iframe=true&amp;width=600&amp;height=550' ),
                esc_attr( sprintf( __( 'More information about %s' ), $plugin_data['Name'] ) ),
                __( 'View details' )
            );
        }
        return $links;
    }
}
