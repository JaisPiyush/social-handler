<?php
/**
 * @package Social Handler
 */
/*
Plugin Name: Social Handler
Plugin URI: https://github.com/JaisPiyush/social-handler-plugin
Description: Used by millions, Akismet is quite possibly the best way in the world to <strong>protect your blog from spam</strong>. It keeps your site protected even while you sleep. To get started: activate the Akismet plugin and then go to your Akismet Settings page to set up your API key.
Version: 1.0.0
Author: Piyush Jaiswal
Author URI: https://automattic.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: social-handler
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2022 Automattic, Inc.
*/

/* exist if directly accessed */
if (! defined('ABSPATH')){
    exit;
}

define("SH_LOCATION",dirname(__FILE__));
define("SH_LOCATION_URL", plugins_url( '', __FILE__ ));


/**
 * Get the registered social profiles
 * @return array : An array of registered social profiles
 */
function sh_get_social_profiles(){
    return apply_filters( 'sh_social_profiles', 
        array()
);
}


function sh_register_default_social_profiles($profiles){
    $profiles["facebook"] = array(
        "id" => "sh_facebook_url",
        "label" => __("Facebook URL", "sh-social-handler-widget"),
        "class" => "facebook",
        "description" => __("Enter your Facebook profile URL", "sh-social-handler-widget"),
        "priority" => 10,
        "type" => "text",
        "default" => "",
        "sanitize_callback" => "sanitize_text_field"
    );
    $profiles["linkedIn"] = array(
        "id" => "sh_linkedIn_url",
        "label" => __("LinkedIn URL", "sh-social-handler-widget"),
        "class" => "linkedIn",
        "description" => __("Enter your LinkedIn profile URL", "sh-social-handler-widget"),
        "priority" => 10,
        "type" => "text",
        "default" => "",
        "sanitize_callback" => "sanitize_text_field"
    );
    return $profiles;
}

add_filter( "sh_social_profiles", "sh_register_default_social_profiles" );

function sh_register_social_customizer_settings($wp_customize) {
    $social_profiles = sh_get_social_profiles();
    if(! empty( $social_profiles )) {
        // register the ustomizer section for social profiles
        $wp_customize->add_section(
            'sh_social',
            array(
                'title' => __("Social Profiles"),
                "description" => __("Add social media profiles here"),
                "priority" => 160,
                "capability" => "edit_theme_options"
            )
            );
        foreach ($social_profiles as $social_profile) {
            $wp_customize->add_setting(
                $social_profile['id'],
                array(
                    'default' => '',
                    'sanitize_callback' => $social_profile['sanitize_callback']
                )
            );

            $wp_customize->add_control(
                $social_profile['id'],
                array(
                    'type' => $social_profile['type'],
                    'priority' => $social_profile['priority'],
                    'section' => 'sh_social',
                    'label' => $social_profile['label'],
                    'description' => $social_profile['description']
                )
            );
        }
    }
}

add_action( 'customize_register', 'sh_register_social_customizer_settings' );

function sh_register_social_icons_widget(){
    register_widget( 'SH_Social_Icons_Widget' );
}

add_action("widgets_init", "sh_register_social_icons_widget");

class SH_Social_Icons_WIdget extends WP_Widget {

    public function __construct() {
        $widget_ops = array(
            "classname" => "sh-social-icons",
            "description" => __("Output your sites social icons", "sh-social-handler-widget"),
        );

        $control_ops = array(
            "id_base" => "sh_social_icons"
        );

        parent::__construct("sh_social_icons", "Social Icons", $widget_ops, $control_ops);

    }

    public function widget($args, $instance) {
        echo wp_kses_post( $args['before_widget'] );
        do_action( 'sh_social_icons_widget_output', $args, $instance );
        echo wp_kses_post( $args["after_widget"] );
    }

    public function form( $instance ){
        $title = ! empty($instance['title']) ? $instance['title'] : '';
        ?>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>">
                <?php esc_attr_e( 'Title:', 'sh-social-handler-widget'); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>

        <p>
            <?php 
            printf(
                __("To add social profiles, please use the social profiles section in the %1$customizer%2$s.",
                "sh-social-handler-widget"),
                '<a href="' . admin_url( 'customize.php') . '">',
                '</a>'
            
            );
            ?>
        </p>
        <?php

    }


    public function update($new_instance, $old_instance){
        $instance = array();
        $instance['title'] = (! empty($new_instance['title'])) ? strip_tags($new_instance['title']): "";
        return $instance;

    }
}
