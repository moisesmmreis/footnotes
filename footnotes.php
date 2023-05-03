<?php
/*
Plugin Name: Footnotes
Plugin URI: https://moisesmmreis.vercel.app
Description: Add a footnote function to the website using Tailwind CSS
Version: 0.1.0
Author: Moisés Moreira Reis
Author URI: https://moisesmmreis.vercel.app
Text Domain: bloatless
*/

// Activation hook
register_activation_hook( __FILE__, 'footnotes_activate' );
function footnotes_activate() {
    // Activation code here
}

// Deactivation hook
register_deactivation_hook( __FILE__, 'footnotes_deactivate' );
function footnotes_deactivate() {
    // Deactivation code here
}

// Enqueue styles
add_action( 'wp_enqueue_scripts', 'footnotes_enqueue_styles' );
function footnotes_enqueue_styles() {
    wp_enqueue_style( 'footnotes-style', plugins_url( 'footnotes.css', __FILE__ ) );
}

// Replace [ref] shortcode with footnote number
add_filter( 'the_content', 'wp_footnotes_replace_shortcode' );
function wp_footnotes_replace_shortcode( $content ) {
    $footnote_count = 0;
    $content = preg_replace_callback( '/\[ref\](.*?)\[\/ref\]/', function( $matches ) use ( &$footnote_count ) {
        $footnote_count++;
        return '<sup id="fn-' . $footnote_count . '"><a href="#fnref-' . $footnote_count . '">' . $footnote_count . '</a></sup><span id="fnref-' . $footnote_count . '" class="footnote">' . $matches[1] . ' <a href="#fn-' . $footnote_count . '">&uarr;</a></span>';
    }, $content );
    return $content;
}

// Add the footnote to the footnote list
function wp_footnote_add_footnote($atts, $content = null) {
    // Get the footnotes from the database
    $footnotes = get_option('wp_footnote_footnotes');
    // Create a new footnote with the content
    $footnote = '<li id="wp-footnote-' . count($footnotes) . '">' . $content . '</li>';
    // Add the footnote to the list
    $footnotes[] = $footnote;
    // Save the footnotes to the database
    update_option('wp_footnote_footnotes', $footnotes);
    // Return the footnote number
    return '<sup><a href="#wp-footnote-' . count($footnotes) . '">' . count($footnotes) . '</a></sup>';
}
add_shortcode('ref', 'wp_footnote_add_footnote');

// Display the footnotes at the end of the content
function wp_footnote_display_footnotes($content) {
    // Get the footnotes from the database
    $footnotes = get_option('wp_footnote_footnotes');
    // If there are no footnotes, return the content as is
    if (empty($footnotes)) {
        return $content;
    }
    // Create the list of footnotes
    $list = '<ol class="wp-footnote-list">';
    foreach ($footnotes as $footnote) {
        $list .= $footnote;
    }
    $list .= '</ol>';
    // Add the footnotes to the end of the content
    $content .= $list;
    // Return the modified content
    return $content;
}
add_filter('the_content', 'wp_footnote_display_footnotes');

function wp_footnote_the_content($content) {
    // Find all footnote shortcodes in the post content
    preg_match_all('/\[ref\](.*?)\[\/ref\]/', $content, $matches);

    // If there are no footnotes, return the content as is
    if (empty($matches[0])) {
        return $content;
    }

    // Initialize an array to store the footnote entries
    $footnotes = array();

    // Loop through each match and generate a footnote entry
    foreach ($matches[1] as $index => $footnote) {
        // Generate the footnote reference number
        $ref_number = $index + 1;

        // Generate the footnote entry
        $footnote_entry = sprintf('<li><a href="#footnote-%d" id="footnote-ref-%d">%d</a>: %s</li>', $ref_number, $ref_number, $ref_number, $footnote);

        // Add the footnote entry to the array
        $footnotes[] = $footnote_entry;

        // Replace the footnote shortcode in the post content with the footnote reference
        $content = str_replace($matches[0][$index], sprintf('<sup id="footnote-%d"><a href="#footnote-ref-%d">%d</a></sup>', $ref_number, $ref_number, $ref_number), $content);
    }

    // If there are no footnotes, return the content as is
    if (empty($footnotes)) {
        return $content;
    }

    // Generate the footnote list HTML
    $footnotes_list_html = '<hr/><h3>Footnotes</h3><ol>'.implode('', $footnotes).'</ol>';

    // Add the footnote list HTML to the end of the content
    $content .= $footnotes_list_html;

    return $content;
}
add_filter('the_content', 'wp_footnote_the_content');
